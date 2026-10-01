<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiQualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token_and_user_without_role_for_customers(): void
    {
        $user = User::factory()->create([
            'email' => 'api@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $this->postJson('/api/login', [
            'email' => 'api@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']])
            ->assertJsonMissingPath('user.role');
    }

    public function test_unverified_user_cannot_access_cart_api(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/cart')->assertForbidden();
    }

    public function test_customer_cannot_view_another_customers_order_via_api(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $intruder = User::factory()->create(['email_verified_at' => now()]);
        $order = $this->createOrder($owner);

        Sanctum::actingAs($intruder);

        $this->getJson('/api/orders/'.$order->id)->assertNotFound();
    }

    public function test_non_admin_cannot_create_product_via_admin_api(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $category = Category::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/admin/products', [
            'category_id' => $category->id,
            'name' => 'Blocked',
            'price' => 9.99,
            'stock' => 1,
            'status' => 'active',
        ])->assertForbidden();
    }

    public function test_non_admin_cannot_create_category_via_admin_api(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        Sanctum::actingAs($user);

        $this->postJson('/api/admin/categories', [
            'name' => 'Blocked category',
        ])->assertForbidden();
    }

    public function test_public_product_api_hides_stock_and_status(): void
    {
        Product::factory()->create([
            'stock' => 42,
            'status' => 'active',
        ]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonMissingPath('data.0.stock')
            ->assertJsonMissingPath('data.0.status')
            ->assertJsonPath('data.0.in_stock', true);
    }

    public function test_public_category_api_hides_products_count(): void
    {
        Category::factory()->create();

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonMissingPath('data.0.products_count');
    }

    public function test_api_cart_rejects_excessive_quantity(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 200, 'status' => 'active']);

        Sanctum::actingAs($user);

        $this->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 500,
        ])->assertUnprocessable();
    }

    public function test_api_cart_store_is_rate_limited(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 1000, 'status' => 'active']);

        Sanctum::actingAs($user);

        for ($i = 0; $i < 30; $i++) {
            $this->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 1,
            ])->assertSuccessful();
        }

        $this->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertStatus(429);
    }

    public function test_order_mass_assignment_cannot_set_protected_status(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $order = $user->orders()->make([
            'shipping_name' => 'Buyer',
            'shipping_line1' => 'Street',
            'shipping_city' => 'City',
            'shipping_postal' => '1',
            'shipping_country' => 'US',
            'status' => Order::STATUS_PAID,
            'total' => 0.01,
            'payment_status' => Order::PAYMENT_STATUS_SUCCEEDED,
            'external_payment_id' => 'pi_forged',
            'paid_at' => now(),
        ]);
        $order->save();
        $order->refresh();

        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame('0.00', (string) $order->total);
        $this->assertNull($order->payment_status);
        $this->assertNull($order->external_payment_id);
        $this->assertNull($order->paid_at);
    }

    public function test_customer_cannot_cancel_order_via_admin_api(): void
    {
        $customer = User::factory()->create(['email_verified_at' => now()]);
        $order = $this->createOrder($customer);

        Sanctum::actingAs($customer);

        $this->postJson('/api/admin/orders/'.$order->id.'/cancel')->assertForbidden();
    }

    public function test_admin_order_list_all_flag_returns_all_orders(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $customer = User::factory()->create(['email_verified_at' => now()]);

        $this->createOrder($admin);
        $this->createOrder($customer);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/orders?all=1')->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_admin_api_category_delete_with_products_returns_unprocessable(): void
    {
        $admin = User::factory()->admin()->create(['email_verified_at' => now()]);
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        Sanctum::actingAs($admin);

        $this->deleteJson('/api/admin/categories/'.$category->id)
            ->assertUnprocessable()
            ->assertJsonPath('message', 'This category still has products. Move or delete them first.');
    }
}
