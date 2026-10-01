<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(private ProductService $products)
    {
    }

    public function home(): View
    {
        $categories = Category::query()
            ->withCount(['products' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('name')
            ->get();

        $featuredProducts = Product::query()
            ->where('status', 'active')
            ->whereIn('name', config('demo.featured_product_names', []))
            ->with('category')
            ->orderBy('name')
            ->take(8)
            ->get();

        $newProducts = Product::query()
            ->where('status', 'active')
            ->where('created_at', '>=', now()->subDays((int) config('demo.new_within_days', 14)))
            ->with('category')
            ->latest()
            ->take(8)
            ->get();

        $products = Product::query()
            ->where('status', 'active')
            ->with('category')
            ->latest()
            ->take(8)
            ->get();

        return view('shop.home', compact('categories', 'products', 'featuredProducts', 'newProducts'));
    }

    public function index(Request $request): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $search = $request->filled('q') ? $request->string('q')->trim()->toString() : null;
        $sort = $request->string('sort', 'latest')->toString();

        $products = $this->products->shopList(
            $request->integer('category_id') ?: null,
            $search !== '' ? $search : null,
            $sort,
        );

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->status === 'active', 404);

        $product->load('category');

        $relatedProducts = Product::query()
            ->where('status', 'active')
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with('category')
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('shop.show', compact('product', 'relatedProducts'));
    }
}
