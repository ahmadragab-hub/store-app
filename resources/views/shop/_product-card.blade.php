<article class="flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
    @if ($product->image_src)
        <img src="{{ $product->image_src }}" alt="{{ $product->name }}" class="h-44 w-full object-cover">
    @else
        <div class="flex h-44 items-center justify-center bg-stone-100 text-sm text-stone-400">No image</div>
    @endif
    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-accent">{{ $product->category?->name }}</p>
        <a href="{{ route('shop.show', $product) }}" class="mt-1 font-semibold text-stone-900 hover:underline">
            {{ $product->name }}
        </a>
        <p class="mt-auto pt-4 text-sm font-medium text-stone-900">${{ number_format((float) $product->price, 2) }}</p>
    </div>
</article>
