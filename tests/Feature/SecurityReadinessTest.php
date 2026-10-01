<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\MockPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_forms_include_csrf_fields(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->get(route('cart.show'))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_customer_cannot_access_admin_product_management_web(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get('/admin/products')
            ->assertForbidden();
    }

    public function test_stripe_webhook_returns_service_unavailable_when_not_configured(): void
    {
        config(['payments.stripe.webhook_secret' => '']);

        $this->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{}',
        )->assertStatus(503);
    }

    public function test_customer_cannot_ship_orders_via_admin_api(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $customer = User::factory()->create(['email_verified_at' => now()]);
        $order = $this->createOrder($admin, ['status' => Order::STATUS_PAID]);

        Sanctum::actingAs($customer);

        $this->postJson('/api/admin/orders/'.$order->id.'/ship')->assertForbidden();
    }

    public function test_api_order_resource_hides_user_id_from_customers(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $order = $this->createOrder($user);

        Sanctum::actingAs($user);

        $this->getJson('/api/orders/'.$order->id)
            ->assertOk()
            ->assertJsonMissingPath('data.user_id');
    }

    public function test_admin_product_create_rejects_invalid_image_upload(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $category = Category::factory()->create();

        Sanctum::actingAs($admin);

        $this->post('/api/admin/products', [
            'category_id' => $category->id,
            'name' => 'Bad upload',
            'price' => 10,
            'stock' => 1,
            'status' => 'active',
            'image' => UploadedFile::fake()->create('evil.php', 100, 'application/x-php'),
        ], [
            'Accept' => 'application/json',
        ])->assertUnprocessable();
    }

    public function test_non_admin_cannot_list_all_orders_via_api_all_flag(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $customer = User::factory()->create(['email_verified_at' => now()]);

        $this->createOrder($admin);
        $this->createOrder($customer);

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/orders?all=1')->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_customer_cannot_update_another_users_cart_item_via_api(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $intruder = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['status' => 'active', 'stock' => 20]);

        Sanctum::actingAs($owner);
        $this->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $cartItemId = $owner->cart()->first()->items()->first()->id;

        Sanctum::actingAs($intruder);

        $this->patchJson('/api/cart/items/'.$cartItemId, [
            'quantity' => 5,
        ])->assertForbidden();
    }

    public function test_customer_cannot_pay_another_users_order_via_api(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $intruder = User::factory()->create(['email_verified_at' => now()]);
        $order = $this->createOrder($owner);

        Sanctum::actingAs($intruder);

        $this->postJson('/api/orders/'.$order->id.'/pay', [
            'card_number' => MockPaymentGateway::TEST_CARD,
        ])->assertNotFound();
    }

    public function test_api_register_ignores_role_mass_assignment(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Attacker',
            'email' => 'attacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertCreated();

        $this->assertSame('customer', User::query()->where('email', 'attacker@example.com')->value('role'));
    }

    public function test_order_item_price_is_not_mass_assignable(): void
    {
        $item = new OrderItem();
        $item->fill([
            'product_id' => 1,
            'quantity' => 2,
            'price' => 0.01,
        ]);

        $this->assertSame(2, $item->quantity);
        $this->assertNull($item->price);
    }

    public function test_stripe_webhook_rejects_missing_signature_header(): void
    {
        config(['payments.stripe.webhook_secret' => 'whsec_test_missing_sig']);

        $this->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{}',
        )->assertStatus(400);
    }
}
