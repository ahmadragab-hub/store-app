<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $carts)
    {
    }

    public function show(): View
    {
        $cart = $this->carts->contents(auth()->user());

        $total = $cart->items->sum(function ($item) {
            return $item->quantity * (float) ($item->product?->price ?? 0);
        });

        return view('cart.show', compact('cart', 'total'));
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $this->carts->add(
            $request->user(),
            $request->integer('product_id'),
            $request->integer('quantity'),
        );

        return redirect()
            ->route('cart.show')
            ->with('success', 'Added to cart.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorize('update', $cartItem);
        $this->carts->updateQuantity(
            $request->user(),
            $cartItem,
            $request->integer('quantity'),
        );

        return redirect()
            ->route('cart.show')
            ->with('success', 'Cart updated.');
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $this->authorize('delete', $cartItem);
        $this->carts->remove(auth()->user(), $cartItem);

        return redirect()
            ->route('cart.show')
            ->with('success', 'Item removed.');
    }
}
