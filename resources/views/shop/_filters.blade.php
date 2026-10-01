@php
    $categoryId = request()->integer('category_id');
@endphp

<div class="store-filters store-surface">
    <h2 class="store-filters__title">Refine results</h2>
    <form method="GET" action="{{ route('shop.index') }}" class="store-filters__form">
        <div>
            <label for="shop-q-{{ $suffix ?? 'main' }}" class="store-label">Search</label>
            <input id="shop-q-{{ $suffix ?? 'main' }}" name="q" type="search" value="{{ request('q') }}" placeholder="Product name or keyword" class="store-input">
        </div>
        @if ($categoryId)
            <input type="hidden" name="category_id" value="{{ $categoryId }}">
        @endif
        <div>
            <label for="shop-sort-{{ $suffix ?? 'main' }}" class="store-label">Sort</label>
            <select id="shop-sort-{{ $suffix ?? 'main' }}" name="sort" class="store-input">
                <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option>
                <option value="name" @selected(request('sort') === 'name')>Name (A–Z)</option>
            </select>
        </div>
        <button type="submit" class="store-button">Apply</button>
    </form>

    @if ($categories->isNotEmpty())
        <div class="store-filters__categories">
            <p class="store-label !mt-0">Categories</p>
            <ul class="store-filters__list">
                <li>
                    <a href="{{ route('shop.index', request()->only(['q', 'sort'])) }}" class="store-category-link {{ $categoryId ? '' : 'is-active' }}">All</a>
                </li>
                @foreach ($categories as $category)
                    <li>
                        <a href="{{ route('shop.index', array_merge(request()->only(['q', 'sort']), ['category_id' => $category->id])) }}" class="store-category-link {{ $categoryId === $category->id ? 'is-active' : '' }}">
                            {{ $category->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
