<?php

namespace Tests;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function createOrder(User $user, array $attributes = []): Order
    {
        $order = $user->orders()->make([
            'shipping_name' => $attributes['shipping_name'] ?? 'Buyer',
            'shipping_line1' => $attributes['shipping_line1'] ?? '1 Main St',
            'shipping_city' => $attributes['shipping_city'] ?? 'Town',
            'shipping_state' => $attributes['shipping_state'] ?? null,
            'shipping_postal' => $attributes['shipping_postal'] ?? '12345',
            'shipping_country' => $attributes['shipping_country'] ?? 'US',
        ]);

        unset(
            $attributes['shipping_name'],
            $attributes['shipping_line1'],
            $attributes['shipping_city'],
            $attributes['shipping_state'],
            $attributes['shipping_postal'],
            $attributes['shipping_country'],
        );

        $order->forceFill(array_merge([
            'total' => 10,
            'status' => Order::STATUS_PENDING,
        ], $attributes))->save();

        return $order->fresh();
    }
}
