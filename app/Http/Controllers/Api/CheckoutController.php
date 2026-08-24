<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkout)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $order = $this->checkout->checkout($request->user());

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }
}
