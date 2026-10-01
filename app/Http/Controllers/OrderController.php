<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private CheckoutService $checkout)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $orders = $request->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return view('orders.index', [
            'orders' => $orders,
            'adminMode' => false,
        ]);
    }

    public function adminIndex(): View
    {
        $this->authorize('manage', Order::class);

        $orders = Order::query()
            ->with(['user', 'items.product'])
            ->latest()
            ->paginate(20);

        return view('orders.index', [
            'orders' => $orders,
            'adminMode' => true,
        ]);
    }

    public function show(Order $order): View
    {
        abort_unless(auth()->user()->can('view', $order), 404);

        $order->load(['items.product', 'user']);

        return view('orders.show', compact('order'));
    }

    public function ship(Order $order): RedirectResponse
    {
        $this->authorize('ship', $order);
        $this->checkout->markShipped($order);

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Order marked as shipped.');
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);
        $this->checkout->cancel($order);

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Order cancelled. Reserved stock was restored when applicable.');
    }
}
