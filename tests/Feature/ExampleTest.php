<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use App\Models\Order;
use App\Services\MockPaymentGateway;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
   
    public function test_is_the_shop_page_returns_a_successful_response(): void
    {
        $response = $this->get('/shop');

        $response->assertStatus(200);
    }
    
    public function test_is_the_products_page_returns_a_successful_response(): void
    {
        $user = User::factory()->admin()->create([
            'email_verified_at' => now(),
        ]);
        $response = $this->actingAs($user)->get('/admin/products');
        $response->assertStatus(200);
    }
    public function test_is_fake_page_returns_a_404_response(): void
    {
        $response = $this->get('/fake-page');
        $response->assertStatus(404);
        $response->assertSee('404');
    }
    public function test_is_product_saved_successfully(): void
    {
        $user = User::factory()->admin()->create([
            'email_verified_at' => now(),
        ]);
        $category = Category::factory()->create([
            'name' => 'Test Category',
        ]);
        $payload = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100,
            'stock' => 10,
            'status' => 'active',
            'category_id' => $category->id,
            'image' => UploadedFile::fake()->image('test.jpg'),
        ];

        $response = $this->actingAs($user)->post('/admin/products', $payload);
        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100,
            'stock' => 10,
            'status' => 'active',
            'category_id' => $category->id,
        ]);
    }
    public function test_is_product_is_not_saved_successfully(): void
    {
        $product = Product::factory()->create([
            'name' => 'Laptop'
        ]);
        $product->delete();

        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    public function test_idor_for_orders(): void
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

        $response = $this->actingAs($intruder)->get("/orders/{$order->id}");
        $response->assertNotFound();
    }

    public function test_payment_is_successful(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 5, 'price' => 20]);

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_name' => 'Test User',
            'shipping_line1' => 'Street 1',
            'shipping_city' => 'City',
            'shipping_postal' => '1000',
            'shipping_country' => 'US',
        ]);

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame(Order::STATUS_PENDING, $order->status);

        $this->actingAs($user)
            ->post(route('checkout.pay.store', $order), [
                'card_number' => MockPaymentGateway::TEST_CARD,
            ])
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame(Order::STATUS_PAID, $order->fresh()->status);
    }
}
