<?php

namespace App\Services;

use App\Exceptions\StoreException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

class CartService
{
    public function getOrCreate(User $user): Cart
    {
        return $user->cart()->firstOrCreate();
    }

    public function contents(User $user): Cart
    {
        $cart = $this->getOrCreate($user);
        $cart->load('items.product');

        return $cart;
    }

    public function add(User $user, int $productId, int $quantity): Cart
    {
        $product = Product::query()
            ->where('status', 'active')
            ->findOrFail($productId);

        $cart = $this->getOrCreate($user);
        $item = $cart->items()->where('product_id', $product->id)->first();
        $newQuantity = ($item?->quantity ?? 0) + $quantity;

        if ($newQuantity > $product->stock) {
            throw new StoreException('Not enough stock for this product.');
        }

        if ($item) {
            $item->update(['quantity' => $newQuantity]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        return $this->contents($user);
    }

    public function updateQuantity(User $user, CartItem $cartItem, int $quantity): Cart
    {
        $cart = $this->ownedCartItem($user, $cartItem);
        $product = $cartItem->product;

        if ($product->status !== 'active') {
            throw new StoreException('This product is no longer available.');
        }

        if ($quantity > $product->stock) {
            throw new StoreException('Not enough stock for this product.');
        }

        $cartItem->update(['quantity' => $quantity]);

        return $this->contents($user);
    }

    public function remove(User $user, CartItem $cartItem): Cart
    {
        $this->ownedCartItem($user, $cartItem);
        $cartItem->delete();

        return $this->contents($user);
    }

    private function ownedCartItem(User $user, CartItem $cartItem): Cart
    {
        $cart = $this->getOrCreate($user);

        abort_unless($cartItem->cart_id === $cart->id, 404);

        return $cart;
    }
}
