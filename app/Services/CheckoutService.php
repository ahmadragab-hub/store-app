<?php

namespace App\Services;

use App\Contracts\PaymentChargeResult;
use App\Contracts\PaymentGateway;
use App\Exceptions\StoreException;
use App\Mail\OrderPaidMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(private PaymentGateway $payments)
    {
    }

    public function checkout(User $user, array $shipping): Order
    {
        return DB::transaction(function () use ($user, $shipping) {
            $cart = $user->cart()->lockForUpdate()->first();

            if (! $cart) {
                throw new StoreException('Your cart is empty.');
            }

            $items = $cart->items()->with('product')->get();

            if ($items->isEmpty()) {
                throw new StoreException('Your cart is empty.');
            }

            $total = 0;

            foreach ($items as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);

                if ($product->status !== 'active') {
                    throw new StoreException("{$product->name} is no longer available.");
                }

                if ($item->quantity > $product->stock) {
                    throw new StoreException("Not enough stock for {$product->name}.");
                }

                $total += $item->quantity * (float) $product->price;
                $item->setRelation('product', $product);
            }

            $expiresMinutes = max(1, (int) config('orders.pending_expires_after_minutes', 60));

            $order = $user->orders()->make([
                'shipping_name' => $shipping['shipping_name'],
                'shipping_line1' => $shipping['shipping_line1'],
                'shipping_city' => $shipping['shipping_city'],
                'shipping_state' => $shipping['shipping_state'] ?? null,
                'shipping_postal' => $shipping['shipping_postal'],
                'shipping_country' => strtoupper($shipping['shipping_country']),
            ]);

            $order->forceFill([
                'total' => $total,
                'status' => Order::STATUS_PENDING,
                'payment_driver' => $this->payments->driver(),
                'expires_at' => now()->addMinutes($expiresMinutes),
            ])->save();

            foreach ($items as $item) {
                $line = $order->items()->make([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                ]);
                $line->forceFill(['price' => $item->product->price]);
                $order->items()->save($line);

                $this->decrementStock($item->product, $item->quantity);
            }

            $cart->items()->delete();

            return $order->load('items.product');
        });
    }

    public function pay(User $user, Order $order, string $paymentToken): Order
    {
        [$order, $result] = DB::transaction(function () use ($user, $order, $paymentToken) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            abort_unless($user->id === $order->user_id, 404);

            if ($order->status === Order::STATUS_PAID) {
                throw new StoreException('This order is already paid.');
            }

            if ($order->status !== Order::STATUS_PENDING) {
                throw new StoreException('This order cannot be paid.');
            }

            if ($order->isExpiredPending()) {
                throw new StoreException('This order has expired. Place a new order to continue.');
            }

            $idempotencyKey = $this->ensurePaymentIdempotencyKey($order);

            $result = $this->payments->charge($paymentToken, (float) $order->total, [
                'order_id' => $order->id,
                'idempotency_key' => $idempotencyKey,
            ]);

            $this->persistPaymentAttempt($order, $result);

            if ($result->status === PaymentChargeResult::STATUS_SUCCEEDED) {
                $paid = $this->applySuccessfulPayment(
                    $order->fresh(),
                    (string) $result->externalPaymentId,
                    $this->payments->driver(),
                );

                return [$paid, $result];
            }

            if ($result->status === PaymentChargeResult::STATUS_FAILED) {
                $this->rotatePaymentIdempotencyKey($order);
            }

            return [$order->fresh(['items.product', 'user']), $result];
        });

        if ($result->status === PaymentChargeResult::STATUS_PROCESSING) {
            throw new StoreException('Payment is processing. You will receive confirmation shortly.');
        }

        if ($result->status === PaymentChargeResult::STATUS_FAILED) {
            $hint = $this->payments->driver() === 'mock'
                ? ' Use test card '.MockPaymentGateway::TEST_CARD.'.'
                : '';

            throw new StoreException('Payment failed.'.$hint);
        }

        return $order;
    }

    public function applySuccessfulPayment(Order $order, string $externalPaymentId, string $driver): Order
    {
        return DB::transaction(function () use ($order, $externalPaymentId, $driver) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($order->status === Order::STATUS_PAID) {
                return $order->fresh(['items.product', 'user']);
            }

            if ($order->status !== Order::STATUS_PENDING) {
                return $order->fresh(['items.product', 'user']);
            }

            if ($order->external_payment_id && $order->external_payment_id !== $externalPaymentId) {
                report(new \RuntimeException('Payment reference mismatch for order #'.$order->id));

                return $order->fresh(['items.product', 'user']);
            }

            $updated = Order::query()
                ->whereKey($order->id)
                ->where('status', Order::STATUS_PENDING)
                ->update([
                    'status' => Order::STATUS_PAID,
                    'paid_at' => now(),
                    'payment_driver' => $driver,
                    'payment_status' => PaymentChargeResult::STATUS_SUCCEEDED,
                    'external_payment_id' => $externalPaymentId,
                    'payment_failure_message' => null,
                    'expires_at' => null,
                ]);

            if ($updated !== 1) {
                return $order->fresh(['items.product', 'user']);
            }

            $order = $order->fresh(['items.product', 'user']);

            if ($order->user?->email) {
                Mail::to($order->user->email)->send(new OrderPaidMail($order));
            }

            return $order;
        });
    }

    public function recordFailedPayment(Order $order, string $externalPaymentId, ?string $message = null): Order
    {
        return DB::transaction(function () use ($order, $externalPaymentId, $message) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($order->status !== Order::STATUS_PENDING) {
                return $order->fresh(['items.product', 'user']);
            }

            $order->forceFill([
                'payment_status' => PaymentChargeResult::STATUS_FAILED,
                'external_payment_id' => $externalPaymentId,
                'payment_failure_message' => $message,
            ])->save();

            $this->rotatePaymentIdempotencyKey($order);

            return $order->fresh(['items.product', 'user']);
        });
    }

    private function ensurePaymentIdempotencyKey(Order $order): string
    {
        if (is_string($order->payment_idempotency_key) && $order->payment_idempotency_key !== '') {
            return $order->payment_idempotency_key;
        }

        $key = 'pay_order_'.$order->id;

        $order->forceFill(['payment_idempotency_key' => $key])->save();

        return $key;
    }

    private function rotatePaymentIdempotencyKey(Order $order): void
    {
        $order->forceFill([
            'payment_idempotency_key' => 'pay_order_'.$order->id.'_'.Str::ulid(),
        ])->save();
    }

    private function persistPaymentAttempt(Order $order, PaymentChargeResult $result): void
    {
        $order->forceFill([
            'payment_status' => $result->status,
            'external_payment_id' => $result->externalPaymentId,
            'payment_failure_message' => $result->failureMessage,
            'payment_driver' => $this->payments->driver(),
        ])->save();
    }

    public function markShipped(Order $order): Order
    {
        if ($order->status !== Order::STATUS_PAID) {
            throw new StoreException('Only paid orders can be marked as shipped.');
        }

        $order->forceFill([
            'status' => Order::STATUS_SHIPPED,
            'shipped_at' => now(),
        ])->save();

        return $order->fresh(['items.product', 'user']);
    }

    public function cancel(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($order->status === Order::STATUS_CANCELLED) {
                throw new StoreException('This order is already cancelled.');
            }

            if (! in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_PAID], true)) {
                throw new StoreException('This order cannot be cancelled.');
            }

            $shouldRestoreStock = $order->status === Order::STATUS_PENDING
                || $order->status === Order::STATUS_PAID;

            if ($shouldRestoreStock) {
                $this->restoreReservedStockOnce($order);
            }

            $order->forceFill([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'expires_at' => null,
            ])->save();

            return $order->fresh(['items.product', 'user']);
        });
    }

    /**
     * Cancel pending orders whose reservation window has elapsed.
     */
    public function expireStalePendingOrders(): int
    {
        $expiredCount = 0;

        Order::query()
            ->where('status', Order::STATUS_PENDING)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $orderId) use (&$expiredCount) {
                try {
                    DB::transaction(function () use ($orderId, &$expiredCount) {
                        $order = Order::query()->lockForUpdate()->find($orderId);

                        if (! $order || $order->status !== Order::STATUS_PENDING || ! $order->isExpiredPending()) {
                            return;
                        }

                        $this->restoreReservedStockOnce($order);

                        $order->forceFill([
                            'status' => Order::STATUS_CANCELLED,
                            'cancelled_at' => now(),
                            'expires_at' => null,
                        ])->save();

                        $expiredCount++;
                    });
                } catch (StoreException) {
                    // Another worker may have cancelled or paid this order.
                }
            });

        return $expiredCount;
    }

    /**
     * Release reserved inventory exactly once (pending expiry/cancel or paid cancel before shipment).
     */
    private function restoreReservedStockOnce(Order $order): void
    {
        $order->refresh();

        if ($order->stock_restored_at !== null) {
            return;
        }

        $order->load('items');

        foreach ($order->items as $item) {
            Product::query()
                ->whereKey($item->product_id)
                ->lockForUpdate()
                ->first()
                ?->increment('stock', $item->quantity);
        }

        $order->forceFill(['stock_restored_at' => now()])->save();
    }

    private function decrementStock(Product $product, int $quantity): void
    {
        $affected = Product::query()
            ->whereKey($product->id)
            ->where('stock', '>=', $quantity)
            ->decrement('stock', $quantity);

        if ($affected < 1) {
            throw new StoreException("Not enough stock for {$product->name}.");
        }

        $product->refresh();
    }
}
