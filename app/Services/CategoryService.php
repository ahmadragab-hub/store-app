<?php

namespace App\Services;

use App\Exceptions\StoreException;
use App\Models\Category;
use App\Support\StoreImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class CategoryService
{
    public function list(): Collection
    {
        return Category::query()
            ->withCount('products')
            ->latest()
            ->get();
    }

    public function find(Category $category): Category
    {
        $category->loadCount('products');

        return $category;
    }

    public function create(array $data, ?UploadedFile $image = null): Category
    {
        if ($image) {
            $data['image'] = StoreImage::store($image, 'categories');
        }

        return Category::query()->create($data);
    }

    public function update(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        if ($image) {
            StoreImage::delete($category->image);
            $data['image'] = StoreImage::store($image, 'categories');
        }

        $category->update($data);

        return $category;
    }

    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            throw new StoreException('This category still has products. Move or delete them first.');
        }

        StoreImage::delete($category->image);
        $category->delete();
    }
}
