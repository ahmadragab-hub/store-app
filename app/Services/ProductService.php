<?php

namespace App\Services;

use App\Exceptions\StoreException;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    public function delete(Product $product): void
    {
        if ($product->orderItems()->exists()) {
            throw new StoreException('This product is on existing orders and cannot be deleted.');
        }

        $product->delete();
    }

    public function shopList(?int $categoryId): LengthAwarePaginator
    {
        return Product::query()
            ->where('status', 'active')
            ->with('category')
            ->when($categoryId, function ($query, $id) {
                $query->where('category_id', $id);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }
}
