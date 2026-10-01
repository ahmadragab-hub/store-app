<?php

namespace Tests\Feature;

use App\Mail\OrderPaidMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\MockPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_to_cart_and_merge_on_login(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $user = User::factory()->create([
            'email' => 'buyer@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect(route('cart.show'));

        $this->assertEquals(2, session('guest_cart')[$product->id] ?? 0);

        $this->post(route('login'), [
            'email' => 'buyer@example.com',
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $this->assertNull(session('guest_cart'));
    }

    public function test_checkout_reserves_stock_and_payment_sends_mail(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 10, 'price' => 25]);

        $this->actingAs($user)
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity' => 3,
            ])
            ->assertRedirect(route('cart.show'));

        $this->actingAs($user)
            ->post(route('checkout.store'), [
                'shipping_name' => 'Ada Lovelace',
                'shipping_line1' => '1 Analytical Engine Way',
                'shipping_city' => 'London',
                'shipping_state' => null,
                'shipping_postal' => 'SW1A',
                'shipping_country' => 'gb',
            ])
            ->assertRedirect();

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame(7, $product->fresh()->stock);

        $this->actingAs($user)
            ->post(route('checkout.pay.store', $order), [
                'card_number' => MockPaymentGateway::TEST_CARD,
            ])
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame(Order::STATUS_PAID, $order->fresh()->status);
        Mail::assertSent(OrderPaidMail::class);
    }

    public function test_cancel_pending_order_restores_stock(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $customer = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 4]);

        $this->actingAs($customer)
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $this->actingAs($customer)
            ->post(route('checkout.store'), [
                'shipping_name' => 'Customer',
                'shipping_line1' => 'Street 1',
                'shipping_city' => 'City',
                'shipping_postal' => '1000',
                'shipping_country' => 'US',
            ]);

        $order = Order::query()->first();
        $this->assertSame(2, $product->fresh()->stock);

        $this->actingAs($admin)
            ->post(route('admin.orders.cancel', $order))
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_customer_cannot_view_another_users_order(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $intruder = User::factory()->create(['email_verified_at' => now()]);

        $order = $this->createOrder($owner, [
            'shipping_name' => 'Owner',
            'shipping_line1' => 'A',
            'shipping_city' => 'B',
            'shipping_postal' => '1',
            'shipping_country' => 'US',
        ]);

        $this->actingAs($intruder)
            ->get(route('orders.show', $order))
            ->assertNotFound();
    }

    public function test_non_admin_cannot_create_product(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $category = Category::factory()->create();

        $this->actingAs($user)
            ->post(route('products.store'), [
                'category_id' => $category->id,
                'name' => 'Hack',
                'price' => 9.99,
                'stock' => 1,
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_registration_ignores_role_mass_assignment(): void
    {
        $this->post(route('register'), [
            'name' => 'Evil',
            'email' => 'evil@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'email' => 'evil@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_security_headers_are_present(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
