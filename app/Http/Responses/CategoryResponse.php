<?php

namespace App\Http\Responses;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;

class CategoryResponse
{
    public function index(Collection $categories): View
    {
        return view('categories.index', compact('categories'));
    }

    public function create(Category $category): View
    {
        return view('categories.create', compact('category'));
    }

    public function show(Category $category): View
    {
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function stored(): RedirectResponse
    {
        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created.');
    }

    public function updated(): RedirectResponse
    {
        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated.');
    }

    public function deleted(): RedirectResponse
    {
        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted.');
    }

    public function failed(string $message): RedirectResponse
    {
        return back()->with('error', $message);
    }
}
