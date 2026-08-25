<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private ProductService $products)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::query()
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);
        return view('products.create', [
            'product' => new Product(['status' => 'active', 'stock' => 0]),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->products->create($request->validated());

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created.');
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);
        $product->load('category');

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update($product, $request->validated());

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $this->products->delete($product);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted.');
    }
}
