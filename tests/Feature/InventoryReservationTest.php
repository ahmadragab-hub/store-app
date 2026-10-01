<?php

namespace Tests\Feature;

use App\Exceptions\StoreException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\MockPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventoryReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_reserves_stock_on_pending_order(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 8]);

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_name' => 'Buyer',
            'shipping_line1' => '1 Main St',
            'shipping_city' => 'Town',
            'shipping_postal' => '12345',
            'shipping_country' => 'US',
        ])->assertRedirect();

        $order = Order::query()->first();
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertNotNull($order->expires_at);
        $this->assertNull($order->stock_restored_at);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_expiration_restores_reserved_stock(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 6]);

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_name' => 'Buyer',
            'shipping_line1' => '1 Main St',
            'shipping_city' => 'Town',
            'shipping_postal' => '12345',
            'shipping_country' => 'US',
        ]);

        $order = Order::query()->first();
        $order->forceFill(['expires_at' => now()->subMinute()])->save();
        $this->assertSame(4, $product->fresh()->stock);

        $this->artisan('orders:expire-pending')->assertSuccessful();

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->stock_restored_at);
        $this->assertSame(6, $product->fresh()->stock);
    }

    public function test_cancelling_pending_order_twice_does_not_double_restore_stock(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $customer = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($customer)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'shipping_name' => 'Buyer',
            'shipping_line1' => '1 Main St',
            'shipping_city' => 'Town',
            'shipping_postal' => '12345',
            'shipping_country' => 'US',
        ]);

        $order = Order::query()->first();

        $this->actingAs($admin)
            ->post(route('admin.orders.cancel', $order))
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame(5, $product->fresh()->stock);

        $this->actingAs($admin)
            ->post(route('admin.orders.cancel', $order))
            ->assertForbidden();

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_payment_cannot_succeed_twice(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 5, 'price' => 10]);

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_name' => 'Buyer',
            'shipping_line1' => '1 Main St',
            'shipping_city' => 'Town',
            'shipping_postal' => '12345',
            'shipping_country' => 'US',
        ]);

        $order = Order::query()->first();

        $this->actingAs($user)
            ->post(route('checkout.pay.store', $order), [
                'card_number' => MockPaymentGateway::TEST_CARD,
            ])
            ->assertRedirect(route('orders.show', $order));

        $this->actingAs($user)
            ->post(route('checkout.pay.store', $order), [
                'card_number' => MockPaymentGateway::TEST_CARD,
            ])
            ->assertNotFound();

        $this->assertSame(Order::STATUS_PAID, $order->fresh()->status);
        $this->assertSame(4, $product->fresh()->stock);

        $this->expectException(StoreException::class);
        $this->expectExceptionMessage('This order is already paid.');

        app(CheckoutService::class)->pay($user, $order->fresh(), MockPaymentGateway::TEST_CARD);
    }

    public function test_paid_order_cancellation_restores_stock_before_shipment(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $customer = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 10, 'price' => 15]);

        $this->actingAs($customer)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 4,
        ]);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'shipping_name' => 'Buyer',
            'shipping_line1' => '1 Main St',
            'shipping_city' => 'Town',
            'shipping_postal' => '12345',
            'shipping_country' => 'US',
        ]);

        $order = Order::query()->first();
        $this->assertSame(6, $product->fresh()->stock);

        $this->actingAs($customer)->post(route('checkout.pay.store', $order), [
            'card_number' => MockPaymentGateway::TEST_CARD,
        ]);

        $this->assertSame(6, $product->fresh()->stock);

        $this->actingAs($admin)
            ->post(route('admin.orders.cancel', $order))
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertNotNull($order->fresh()->stock_restored_at);
    }

    public function test_concurrent_checkouts_cannot_drive_stock_negative(): void
    {
        $product = Product::factory()->create(['stock' => 1, 'price' => 20]);
        $userA = User::factory()->create(['email_verified_at' => now()]);
        $userB = User::factory()->create(['email_verified_at' => now()]);

        foreach ([$userA, $userB] as $user) {
            $this->actingAs($user)->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);
        }

        $shipping = [
            'shipping_name' => 'Buyer',
            'shipping_line1' => '1 Main St',
            'shipping_city' => 'Town',
            'shipping_postal' => '12345',
            'shipping_country' => 'US',
        ];

        $successes = 0;
        $failures = 0;

        foreach ([$userA, $userB] as $user) {
            try {
                DB::transaction(function () use ($user, $shipping) {
                    app(CheckoutService::class)->checkout($user, $shipping);
                });
                $successes++;
            } catch (StoreException) {
                $failures++;
            }
        }

        $this->assertSame(1, $successes);
        $this->assertSame(1, $failures);
        $this->assertSame(0, $product->fresh()->stock);
        $this->assertSame(1, Order::query()->where('status', Order::STATUS_PENDING)->count());
    }
}
