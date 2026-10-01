<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->isAdmin();
    }

    public function pay(User $user, Order $order): bool
    {
        return $user->id === $order->user_id
            && $order->status === Order::STATUS_PENDING
            && ! $order->isExpiredPending();
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }

    public function ship(User $user, Order $order): bool
    {
        return $user->isAdmin() && $order->status === Order::STATUS_PAID;
    }

    public function cancel(User $user, Order $order): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        return in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_PAID], true);
    }
}
