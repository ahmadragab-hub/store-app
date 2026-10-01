<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Requests\UpdateGuestCartItemRequest;
use App\Models\CartItem;
use App\Services\CartService;
use App\Services\GuestCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $carts,
        private GuestCartService $guestCarts,
    ) {
    }

    public function show(): View
    {
        if (auth()->check()) {
            $cart = $this->carts->contents(auth()->user());
            $items = $cart->items;
            $total = $items->sum(fn ($item) => $item->quantity * (float) ($item->product?->price ?? 0));

            return view('cart.show', [
                'items' => $items,
                'total' => $total,
                'guest' => false,
            ]);
        }

        $items = collect($this->guestCarts->contents());
        $total = $items->sum(fn ($item) => $item->quantity * (float) $item->product->price);

        return view('cart.show', [
            'items' => $items,
            'total' => $total,
            'guest' => true,
        ]);
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        if ($request->user()) {
            $this->carts->add(
                $request->user(),
                $request->integer('product_id'),
                $request->integer('quantity'),
            );
        } else {
            $this->guestCarts->add(
                $request->integer('product_id'),
                $request->integer('quantity'),
            );
        }

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

    public function updateGuest(UpdateGuestCartItemRequest $request, int $productId): RedirectResponse
    {
        $this->guestCarts->update($productId, $request->integer('quantity'));

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

    public function destroyGuest(int $productId): RedirectResponse
    {
        $this->guestCarts->remove($productId);

        return redirect()
            ->route('cart.show')
            ->with('success', 'Item removed.');
    }
}
