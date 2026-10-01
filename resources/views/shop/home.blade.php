@extends('layouts.app')

@section('title', config('app.name'))

@section('mainClass', 'store-main store-main--flush')

@section('hero')
    @php
        use App\Support\StoreImage;
        $heroImages = [
            StoreImage::url('products/demo_product_00.jpg'),
            StoreImage::url('products/demo_product_04.jpg'),
            StoreImage::url('products/demo_product_08.jpg'),
        ];
        $slides = [
            ['image' => $heroImages[0], 'headline' => 'Curated products for the way you live and work.', 'cta' => 'Shop collection', 'cta_url' => route('shop.index')],
            ['image' => $heroImages[1], 'headline' => 'From everyday essentials to considered upgrades.', 'cta' => 'Browse categories', 'cta_url' => route('home').'#categories'],
            ['image' => $heroImages[2], 'headline' => 'Guest-friendly cart. Secure checkout when you are ready.', 'cta' => 'Start shopping', 'cta_url' => route('shop.index')],
        ];
    @endphp

    <section class="hero-slider border-b border-line" data-hero-slider tabindex="0" aria-roledescription="carousel" aria-label="Featured">
        <div class="hero-slider__viewport">
            <div class="hero-slider__track" data-hero-track>
                @foreach ($slides as $i => $slide)
                    <article class="hero-slider__slide" data-hero-slide aria-hidden="{{ $i === 0 ? 'false' : 'true' }}">
                        @if ($slide['image'])
                            <img src="{{ $slide['image'] }}" alt="" class="hero-slider__image" draggable="false" @if ($i > 0) loading="lazy" @endif>
                        @endif
                        <div class="hero-slider__shade"></div>
                        <div class="store-shell relative flex min-h-[min(72vh,680px)] flex-col justify-end pb-16 pt-24 sm:pb-20">
                            <p class="store-eyebrow !text-white/70">Next-gen retail</p>
                            <h2 class="font-display mt-4 max-w-3xl text-4xl font-semibold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                                {{ config('app.name') }}
                            </h2>
                            <p class="mt-5 max-w-xl text-base leading-relaxed text-white/85 sm:text-lg">{{ $slide['headline'] }}</p>
                            <div class="mt-8 flex flex-wrap gap-3">
                                <a href="{{ $slide['cta_url'] }}" class="store-button-inline !bg-white !text-ink hover:!bg-white/90">{{ $slide['cta'] }}</a>
                                <a href="{{ route('shop.index') }}" class="store-button-ghost !border-white/30 !bg-transparent !text-white hover:!bg-white/10">View catalog</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
        <div class="store-shell pointer-events-none absolute inset-x-0 bottom-6 z-20 flex justify-between sm:bottom-8">
            <div class="pointer-events-auto flex gap-2" role="tablist">
                @foreach ($slides as $i => $slide)
                    <button type="button" class="hero-slider__dot" data-hero-dot="{{ $i }}" aria-label="Slide {{ $i + 1 }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
            <div class="pointer-events-auto flex gap-2">
                <button type="button" class="hero-slider__nav" data-hero-prev aria-label="Previous"><span aria-hidden="true">←</span></button>
                <button type="button" class="hero-slider__nav" data-hero-next aria-label="Next"><span aria-hidden="true">→</span></button>
            </div>
        </div>
    </section>

    <section class="store-trust">
        <div class="store-shell grid gap-8 py-10 sm:grid-cols-3">
            <div class="store-trust-item"><p class="font-semibold text-ink">Secure payments</p><p class="mt-1 text-sm text-muted">Protected checkout with clear order status.</p></div>
            <div class="store-trust-item"><p class="font-semibold text-ink">Reserved stock</p><p class="mt-1 text-sm text-muted">Inventory held when your order is placed.</p></div>
            <div class="store-trust-item"><p class="font-semibold text-ink">Fast support</p><p class="mt-1 text-sm text-muted">Track shipments from your account.</p></div>
        </div>
    </section>
@endsection

@section('content')
    @if ($categories->isNotEmpty())
        <section id="categories" class="store-shell store-section">
            <div class="store-section-header">
                <div>
                    <p class="store-eyebrow">Discover</p>
                    <h2 class="store-title mt-2 text-2xl sm:text-3xl">Shop by category</h2>
                </div>
                <a href="{{ route('shop.index') }}" class="store-link text-sm">Full catalog</a>
            </div>
            <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories->take(9) as $category)
                    <a href="{{ route('shop.index', ['category_id' => $category->id]) }}" class="store-category-card">
                        <span class="font-medium text-ink">{{ $category->name }}</span>
                        <span class="text-sm text-muted">{{ $category->products_count }} items</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($featuredProducts->isNotEmpty())
        <section class="store-shell store-section !pt-0">
            <div class="store-section-header">
                <div>
                    <p class="store-eyebrow">Highlighted</p>
                    <h2 class="store-title mt-2 text-2xl sm:text-3xl">Featured products</h2>
                </div>
            </div>
            <div class="store-product-grid mt-10">
                @foreach ($featuredProducts as $product)
                    @include('shop._product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    @if ($newProducts->isNotEmpty())
        <section class="store-shell store-section !pt-0">
            <div class="store-section-header">
                <div>
                    <p class="store-eyebrow">Just in</p>
                    <h2 class="store-title mt-2 text-2xl sm:text-3xl">New arrivals</h2>
                </div>
                <a href="{{ route('shop.index', ['sort' => 'latest']) }}" class="store-link text-sm">Shop new</a>
            </div>
            <div class="store-product-grid mt-10">
                @foreach ($newProducts as $product)
                    @include('shop._product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    <section class="store-shell store-section !pt-0">
        <div class="store-cta-band">
            <p class="store-eyebrow">Ready when you are</p>
            <h2 class="font-display mt-3 text-2xl font-semibold text-ink sm:text-3xl">Build your cart in minutes</h2>
            <p class="store-lede mx-auto">Explore {{ $products->count() > 0 ? 'hundreds of' : '' }} curated SKUs with transparent stock and pricing.</p>
            <a href="{{ route('shop.index') }}" class="store-button-inline mt-8">Shop now</a>
        </div>
    </section>
@endsection
