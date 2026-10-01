@csrf

@if ($product->exists)
    @method('PUT')
@endif

<div>
    <label for="category_id" class="store-label">Category</label>
    <select id="category_id" name="category_id" required class="store-input">
        <option value="">Select a category</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    @error('category_id')
        <p class="store-error">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="name" class="store-label">Name</label>
    <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" required class="store-input">
    @error('name')
        <p class="store-error">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="description" class="store-label">Description</label>
    <textarea id="description" name="description" rows="4" class="store-input">{{ old('description', $product->description) }}</textarea>
    @error('description')
        <p class="store-error">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="image" class="store-label">Image upload</label>
    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="store-input">
    <p class="mt-1 text-xs text-stone-500">JPG, PNG, or WebP. Max 2MB. Stored with a hashed filename.</p>
    @if ($product->image_src)
        <img src="{{ $product->image_src }}" alt="" class="mt-3 h-24 w-24 rounded-lg object-cover">
    @endif
    @error('image')
        <p class="store-error">{{ $message }}</p>
    @enderror
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label for="price" class="store-label">Price</label>
        <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $product->price) }}" required class="store-input">
        @error('price')
            <p class="store-error">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label for="stock" class="store-label">Stock</label>
        <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required class="store-input">
        @error('stock')
            <p class="store-error">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label for="status" class="store-label">Status</label>
        <select id="status" name="status" required class="store-input">
            <option value="active" @selected(old('status', $product->status) === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option>
        </select>
        @error('status')
            <p class="store-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex flex-col gap-3 sm:flex-row sm:items-center">
    <button type="submit" class="store-button sm:w-auto">{{ $button }}</button>
    <a href="{{ route('products.index') }}" class="text-center text-sm font-medium text-stone-600 hover:text-stone-900">Cancel</a>
</div>
