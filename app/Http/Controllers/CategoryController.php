<?php

namespace App\Http\Controllers;

use App\Exceptions\StoreException;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Responses\CategoryResponse;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categories,
        private CategoryResponse $responses,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Category::class);
        return $this->responses->index($this->categories->list());
    }

    public function create(): View
    {
        $this->authorize('create', Category::class);
        return $this->responses->create(new Category());
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated());

        return $this->responses->stored();
    }

    public function show(Category $category): View
    {
        $this->authorize('view', $category);
        return $this->responses->show($this->categories->find($category));
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);
        return $this->responses->edit($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categories->update($category, $request->validated());

        return $this->responses->updated();
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        try {
            $this->categories->delete($category);
        } catch (StoreException $e) {
            return $this->responses->failed($e->getMessage());
        }

        return $this->responses->deleted();
    }
}
