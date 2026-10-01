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
        // get the product by id and check if it is active
        $product = Product::query()
            ->where('status', 'active')
            ->findOrFail($productId);

        // get the cart by user id and check if it is owned by the user
        $cart = $this->getOrCreate($user);
        // get the item by product id and check if it is in the cart and get the quantity
        $item = $cart->items()->where('product_id', $product->id)->first();
        // calculate the new quantity
        $newQuantity = ($item?->quantity ?? 0) + $quantity;

        // check if the new quantity is greater than the stock
        if ($newQuantity > $product->stock) {
            throw new StoreException('Not enough stock for this product.');
        }

        // if the item is in the cart, update the quantity
        if ($item) {
            $item->update(['quantity' => $newQuantity]);
        } else {
            // if the item is not in the cart, create a new item
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        // return the contents of the cart
        return $this->contents($user);
    }

    public function updateQuantity(User $user, CartItem $cartItem, int $quantity): Cart
    {
        $this->ownedCartItem($user, $cartItem);
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
    
    private function ownedCartItem(User $user, CartItem $cartItem): void
    {
        abort_unless($cartItem->cart?->user_id === $user->id, 404);
    }
}
