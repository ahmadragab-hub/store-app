<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\StoreException;
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
        $search = $request->filled('q') ? $request->string('q')->trim()->toString() : null;

        $products = $this->products->shopList(
            $request->integer('category_id') ?: null,
            $search !== '' ? $search : null,
            $request->string('sort', 'latest')->toString(),
        );

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        $product = $this->products->create(
            $request->productData(),
            $request->file('image'),
        );
        $product->load('category');

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        abort_unless($product->status === 'active' || auth()->user()?->isAdmin(), 404);

        $product->load('category');

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $this->authorize('update', $product);
        $product = $this->products->update(
            $product,
            $request->productData(),
            $request->file('image'),
        );
        $product->load('category');

        return new ProductResource($product);
    }

    public function destroy(Product $product): Response|JsonResponse
    {
        $this->authorize('delete', $product);

        try {
            $this->products->delete($product);
        } catch (StoreException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->noContent();
    }
}
