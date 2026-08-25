<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(private ProductService $products)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->where('status', 'active')
            ->with('category')
            ->when($request->integer('category_id'), function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(10);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        $product = $this->products->create($request->validated());
        $product->load('category');

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        abort_unless($product->status === 'active', 404);

        $product->load('category');

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $this->authorize('update', $product);
        $product = $this->products->update($product, $request->validated());
        $product->load('category');

        return new ProductResource($product);
    }

    public function destroy(Product $product): Response
    {
        $this->authorize('delete', $product);
        $this->products->delete($product);

        return response()->noContent();
    }
}
