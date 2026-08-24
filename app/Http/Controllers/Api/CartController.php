<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $carts)
    {
    }

    public function show(Request $request): CartResource
    {
        return new CartResource($this->carts->contents($request->user()));
    }

    public function store(AddToCartRequest $request): CartResource
    {
        $cart = $this->carts->add(
            $request->user(),
            $request->integer('product_id'),
            $request->integer('quantity'),
        );

        return new CartResource($cart);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): CartResource
    {
        $cart = $this->carts->updateQuantity(
            $request->user(),
            $cartItem,
            $request->integer('quantity'),
        );

        return new CartResource($cart);
    }

    public function destroy(Request $request, CartItem $cartItem): CartResource
    {
        return new CartResource($this->carts->remove($request->user(), $cartItem));
    }
}
