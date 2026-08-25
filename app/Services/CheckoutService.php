<?php

namespace App\Services;

use App\Exceptions\StoreException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(private MockPaymentGateway $payments)
    {
    }

    public function checkout(User $user): Order
    {
        return DB::transaction(function () use ($user) {
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

            $order = $user->orders()->create([
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            $cart->items()->delete();

            return $order->load('items.product');
        });
    }

    public function pay(User $user, Order $order, string $cardNumber): Order
    {
        abort_unless($user->id === $order->user_id, 404);

        if ($order->status === 'paid') {
            throw new StoreException('This order is already paid.');
        }

        if ($order->status !== 'pending') {
            throw new StoreException('This order cannot be paid.');
        }

        if (! $this->payments->charge($cardNumber, (float) $order->total)) {
            throw new StoreException('Payment failed. Use test card 4242 4242 4242 4242.');
        }

        $order->update(['status' => 'paid']);

        return $order->fresh(['items.product']);
    }
}
