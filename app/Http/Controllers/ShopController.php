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
            ->latest()
            ->get();

        $products = Product::query()
            ->where('status', 'active')
            ->with('category')
            ->latest()
            ->take(8)
            ->get();

        return view('shop.home', compact('categories', 'products'));
    }

    public function index(Request $request): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $products = $this->products->shopList($request->integer('category_id') ?: null);

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->status === 'active', 404);

        $product->load('category');

        return view('shop.show', compact('product'));
    }
}
