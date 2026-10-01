<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(private CheckoutService $checkout)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Order::class);

        if ($request->user()->isAdmin() && $request->boolean('all')) {
            $orders = Order::query()
                ->with(['user', 'items.product'])
                ->latest()
                ->paginate(20);
        } else {
            $orders = $request->user()
                ->orders()
                ->with('items.product')
                ->latest()
                ->paginate(10);
        }

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        abort_unless($request->user()->can('view', $order), 404);

        $order->load(['items.product', 'user']);

        return new OrderResource($order);
    }

    public function ship(Request $request, Order $order): OrderResource
    {
        $this->authorize('ship', $order);

        return new OrderResource($this->checkout->markShipped($order));
    }

    public function cancel(Request $request, Order $order): OrderResource
    {
        $this->authorize('cancel', $order);

        return new OrderResource($this->checkout->cancel($order));
    }
}
