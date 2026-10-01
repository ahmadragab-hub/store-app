<?php

namespace App\Services;

use App\Exceptions\StoreException;
use App\Models\Product;
use App\Support\StoreImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class ProductService
{
    public function create(array $data, ?UploadedFile $image = null): Product
    {
        if ($image) {
            $data['image'] = StoreImage::store($image, 'products');
        }

        return Product::query()->create($data);
    }

    public function update(Product $product, array $data, ?UploadedFile $image = null): Product
    {
        if ($image) {
            StoreImage::delete($product->image);
            $data['image'] = StoreImage::store($image, 'products');
        }

        $product->update($data);

        return $product;
    }

    public function delete(Product $product): void
    {
        if ($product->orderItems()->exists()) {
            throw new StoreException('This product is on existing orders and cannot be deleted.');
        }

        StoreImage::delete($product->image);
        $product->delete();
    }

    public function shopList(?int $categoryId, ?string $search = null, string $sort = 'latest'): LengthAwarePaginator
    {
        $sort = in_array($sort, ['latest', 'price_asc', 'price_desc', 'name'], true)
            ? $sort
            : 'latest';

        return Product::query()
            ->where('status', 'active')
            ->with('category')
            ->when($categoryId, function ($query, $id) {
                $query->where('category_id', $id);
            })
            ->when($search, function ($query, $term) {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            })
            ->when($sort === 'price_asc', fn ($query) => $query->orderBy('price'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByDesc('price'))
            ->when($sort === 'name', fn ($query) => $query->orderBy('name'))
            ->when($sort === 'latest', fn ($query) => $query->latest())
            ->paginate(12)
            ->withQueryString();
    }
}
