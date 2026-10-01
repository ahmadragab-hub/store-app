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
    <label for="image" class="store-label">Image upload</label>
    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="store-input">
    <p class="mt-1 text-xs text-stone-500">JPG, PNG, or WebP. Max 2MB.</p>
    @if ($category->image_src)
        <img src="{{ $category->image_src }}" alt="" class="mt-3 h-24 w-24 rounded-lg object-cover">
    @endif
    @error('image')
        <p class="store-error">{{ $message }}</p>
    @enderror
</div>

<div class="flex flex-col gap-3 sm:flex-row sm:items-center">
    <button type="submit" class="store-button sm:w-auto">{{ $button }}</button>
    <a href="{{ route('categories.index') }}" class="text-center text-sm font-medium text-stone-600 hover:text-stone-900">Cancel</a>
</div>
