<?php

namespace Database\Seeders;

use App\Contracts\PaymentChargeResult;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $demoCustomer = User::query()->where('email', 'customer@store.test')->firstOrFail();

        $this->seedCart($demoCustomer, [
            'NovaSound Wireless Earbuds' => 1,
            'NoteWell Dot Grid Notebook' => 2,
            'Mountain Roast Coffee Beans' => 1,
            'HydroTrack Steel Bottle' => 1,
        ]);

        $this->seedDemoCustomerOrders($demoCustomer);

        $emptyCartUser = $this->user('alex.morgan@store.test', 'Alex Morgan');
        Cart::query()->firstOrCreate(['user_id' => $emptyCartUser->id]);

        $cartOnlyUser = $this->user('riley.chen@store.test', 'Riley Chen');
        $this->seedCart($cartOnlyUser, [
            'PulseFit Smart Watch' => 1,
            'BrightLite Desk Lamp' => 1,
        ]);

        $multiOrderUser = $this->user('casey.nguyen@store.test', 'Casey Nguyen');
        $this->seedOrder($multiOrderUser, Order::STATUS_SHIPPED, [
            ['name' => 'TrailFlex Hiking Boots', 'qty' => 1],
            ['name' => 'Summit Trekking Poles', 'qty' => 1],
        ], Carbon::now()->subMonths(4)->subDays(3));

        $this->seedOrder($multiOrderUser, Order::STATUS_PAID, [
            ['name' => 'StarQuest Board Game', 'qty' => 2],
        ], Carbon::now()->subMonths(2)->subDays(10));

        $this->seedOrder($multiOrderUser, Order::STATUS_PENDING, [
            ['name' => 'CoreBalance Yoga Mat', 'qty' => 1],
        ], Carbon::now()->subDays(2));

        foreach ([
            ['email' => 'jordan.reed@store.test', 'name' => 'Jordan Reed'],
            ['email' => 'sam.lopez@store.test', 'name' => 'Sam Lopez'],
            ['email' => 'taylor.brooks@store.test', 'name' => 'Taylor Brooks'],
        ] as $profile) {
            $this->user($profile['email'], $profile['name']);
        }
    }

    private function user(string $email, string $name): User
    {
        $user = User::query()->firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'password' => 'password',
            'role' => 'customer',
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    /**
     * @param  array<string, int>  $lines  product name => quantity
     */
    private function seedCart(User $user, array $lines): void
    {
        $cart = Cart::query()->firstOrCreate(['user_id' => $user->id]);

        foreach ($lines as $productName => $quantity) {
            $product = Product::query()->where('name', $productName)->firstOrFail();

            CartItem::query()->updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                ],
                ['quantity' => $quantity],
            );
        }
    }

    private function seedDemoCustomerOrders(User $user): void
    {
        $this->seedOrder($user, Order::STATUS_PENDING, [
            ['name' => 'ClearView 27" Monitor', 'qty' => 1],
            ['name' => 'CableNest Desk Organizer', 'qty' => 1],
        ], Carbon::now()->subDays(1), pendingCheckout: true);

        $this->seedOrder($user, Order::STATUS_PAID, [
            ['name' => 'BrewCraft Pour-Over Kettle', 'qty' => 1],
        ], Carbon::now()->subMonths(1)->subDays(5));

        $this->seedOrder($user, Order::STATUS_SHIPPED, [
            ['name' => 'UrbanWeave Crew Sweater', 'qty' => 1],
            ['name' => 'Coastline Linen Shirt', 'qty' => 2],
            ['name' => 'Golden Valley Honey Jar', 'qty' => 1],
        ], Carbon::now()->subMonths(3)->subDays(8));

        $this->seedOrder($user, Order::STATUS_CANCELLED, [
            ['name' => 'FlexBand Resistance Set', 'qty' => 1],
        ], Carbon::now()->subMonths(2)->subDays(20), cancelled: true);

        $this->seedOrder($user, Order::STATUS_PAID, [
            ['name' => 'HappyPaws Dry Food 12lb', 'qty' => 1],
            ['name' => 'FetchMaster Rope Toy', 'qty' => 3],
        ], Carbon::now()->subMonths(5)->subDays(2));
    }

    /**
     * @param  list<array{name: string, qty: int}>  $lines
     */
    private function seedOrder(
        User $user,
        string $status,
        array $lines,
        Carbon $placedAt,
        bool $pendingCheckout = false,
        bool $cancelled = false,
    ): Order {
        $items = [];
        $total = 0.0;

        foreach ($lines as $line) {
            $product = Product::query()->where('name', $line['name'])->firstOrFail();
            $price = (float) $product->price;
            $total += $line['qty'] * $price;
            $items[] = [
                'product_id' => $product->id,
                'quantity' => $line['qty'],
                'price' => $price,
            ];
        }

        $order = $user->orders()->make([
            'shipping_name' => $user->name,
            'shipping_line1' => '742 Market Street',
            'shipping_city' => 'Springfield',
            'shipping_state' => 'IL',
            'shipping_postal' => '62701',
            'shipping_country' => 'US',
        ]);

        $attributes = [
            'total' => round($total, 2),
            'status' => $status,
            'payment_driver' => 'mock',
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ];

        if ($status === Order::STATUS_PENDING && $pendingCheckout) {
            $attributes['expires_at'] = now()->addHours(12);
        }

        if (in_array($status, [Order::STATUS_PAID, Order::STATUS_SHIPPED], true)) {
            $paidAt = (clone $placedAt)->addHours(2);
            $attributes['paid_at'] = $paidAt;
            $attributes['payment_status'] = PaymentChargeResult::STATUS_SUCCEEDED;
            $attributes['external_payment_id'] = 'demo_mock_pi_'.Str::lower(Str::random(16));
            $attributes['payment_idempotency_key'] = 'demo_pay_'.Str::ulid();
        }

        if ($status === Order::STATUS_SHIPPED) {
            $attributes['shipped_at'] = (clone $placedAt)->addDays(2);
        }

        if ($cancelled || $status === Order::STATUS_CANCELLED) {
            $attributes['status'] = Order::STATUS_CANCELLED;
            $attributes['cancelled_at'] = (clone $placedAt)->addDay();
            $attributes['stock_restored_at'] = (clone $placedAt)->addDay();
        }

        $order->forceFill($attributes)->save();

        foreach ($items as $item) {
            $line = $order->items()->make([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
            $line->forceFill(['price' => $item['price']]);
            $order->items()->save($line);
        }

        return $order->fresh();
    }
}
