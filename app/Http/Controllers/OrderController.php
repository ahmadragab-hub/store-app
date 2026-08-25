<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Order::class);
        $orders = auth()->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        abort_unless(auth()->user()->can('view', $order), 404);

        $order->load('items.product');

        return view('orders.show', compact('order'));
    }
}
