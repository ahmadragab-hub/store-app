<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\PayOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkout)
    {
    }

    public function store(CheckoutRequest $request): JsonResponse
    {
        $order = $this->checkout->checkout($request->user(), $request->shipping());

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function pay(PayOrderRequest $request, Order $order): OrderResource
    {
        abort_unless($request->user()->can('pay', $order), 404);

        $order = $this->checkout->pay(
            $request->user(),
            $order,
            $request->paymentToken(),
        );

        return new OrderResource($order);
    }
}
