<?php

namespace App\Http\Controllers;

use App\Exceptions\StoreException;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categories)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        return view('categories.index', [
            'categories' => $this->categories->list(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('categories.create', [
            'category' => new Category(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categories->create(
            $request->categoryData(),
            $request->file('image'),
        );

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created.');
    }

    public function show(Category $category): View
    {
        $this->authorize('view', $category);

        return view('categories.show', [
            'category' => $this->categories->find($category),
        ]);
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categories->update(
            $category,
            $request->categoryData(),
            $request->file('image'),
        );

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        try {
            $this->categories->delete($category);
        } catch (StoreException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted.');
    }
}
