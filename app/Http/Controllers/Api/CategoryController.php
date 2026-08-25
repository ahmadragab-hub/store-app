<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categories)
    {
    }

    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection($this->categories->list());
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);
        $category = $this->categories->create($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category): CategoryResource
    {
        $category->load(['products' => function ($query) {
            $query->where('status', 'active');
        }]);

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $this->authorize('update', $category);
        return new CategoryResource($this->categories->update($category, $request->validated()));
    }

    public function destroy(Category $category): Response
    {
        $this->authorize('delete', $category);
        $this->categories->delete($category);

        return response()->noContent();
    }
}
