<?php

namespace App\Services;

use App\Exceptions\StoreException;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class GuestCartService
{
    private const SESSION_KEY = 'guest_cart';

    public function items(): array
    {
        $items = Session::get(self::SESSION_KEY, []);

        if (! is_array($items)) {
            return [];
        }

        $clean = [];

        foreach ($items as $productId => $quantity) {
            $id = (int) $productId;
            $qty = (int) $quantity;

            if ($id > 0 && $qty > 0) {
                $clean[$id] = $qty;
            }
        }

        return $clean;
    }

    public function sum(): int
    {
        return array_sum($this->items());
    } 

    public function add(int $productId, int $quantity): void
    {
        $product = Product::query()
            ->where('status', 'active')
            ->findOrFail($productId);

        $items = $this->items();
        $newQuantity = ($items[$product->id] ?? 0) + $quantity;

        if ($newQuantity > $product->stock) {
            throw new StoreException('Not enough stock for this product.');
        }

        $items[$product->id] = $newQuantity;
        Session::put(self::SESSION_KEY, $items);
    }

    public function update(int $productId, int $quantity): void
    {
        $product = Product::query()
            ->where('status', 'active')
            ->findOrFail($productId);

        if ($quantity > $product->stock) {
            throw new StoreException('Not enough stock for this product.');
        }

        $items = $this->items();

        if (! isset($items[$productId])) {
            abort(404);
        }

        $items[$productId] = $quantity;
        Session::put(self::SESSION_KEY, $items);
    }

    public function remove(int $productId): void
    {
        $items = $this->items();
        unset($items[$productId]);
        Session::put(self::SESSION_KEY, $items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }


    public function contents(): array
    {
        $items = $this->items();

        if ($items === []) {
            return [];
        }

        $products = Product::query()
            ->whereIn('id', array_keys($items))
            ->with('category')
            ->get()
            ->keyBy('id');

        $rows = [];

        foreach ($items as $productId => $quantity) {
            $product = $products->get($productId);

            if (! $product) {
                continue;
            }

            $rows[] = (object) [
                'product_id' => $productId,
                'quantity' => $quantity,
                'product' => $product,
            ];
        }

        return $rows;
    }
    
    /* merge the guest cart into the user cart */
    public function mergeInto(User $user, CartService $carts): void
    {
        foreach ($this->items() as $productId => $quantity) {
            try {
                $carts->add($user, $productId, $quantity);
            } catch (StoreException) {
                // Skip items that no longer fit stock after login.
            }
        }

        $this->clear();
    }
}
