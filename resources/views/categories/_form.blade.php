@csrf

@if ($category->exists)
    @method('PUT')
@endif

<div>
    <label for="name" class="store-label">Name</label>
    <input id="name" name="name" type="text" value="{{ old('name', $category->name) }}" required autofocus class="store-input">
    @error('name')
        <p class="store-error">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="image" class="store-label">Image URL</label>
    <input id="image" name="image" type="text" value="{{ old('image', $category->image) }}" class="store-input" placeholder="https://example.com/category.jpg">
    @error('image')
        <p class="store-error">{{ $message }}</p>
    @enderror
</div>

<div class="flex flex-col gap-3 sm:flex-row sm:items-center">
    <button type="submit" class="store-button sm:w-auto">{{ $button }}</button>
    <a href="{{ route('categories.index') }}" class="text-center text-sm font-medium text-stone-600 hover:text-stone-900">Cancel</a>
</div>
