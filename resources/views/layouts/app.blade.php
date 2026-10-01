<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|space-grotesk:500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="store-body font-sans antialiased">
    <header class="store-header sticky top-0 z-40">
        <div class="store-shell">
            <div class="flex h-16 items-center gap-3 lg:h-[4.25rem]">
                <a href="{{ route('home') }}" class="store-brand shrink-0">{{ config('app.name') }}</a>

                <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary">
                    <a href="{{ route('shop.index') }}" class="store-nav-link {{ request()->routeIs('shop.*') ? 'is-active' : '' }}">Shop</a>
                    <a href="{{ route('home') }}#categories" class="store-nav-link">Categories</a>
                </nav>

                <form method="GET" action="{{ route('shop.index') }}" class="mx-auto hidden max-w-md flex-1 lg:block" role="search">
                    <label for="header-search" class="sr-only">Search products</label>
                    <input id="header-search" name="q" type="search" value="{{ request()->routeIs('shop.index') ? request('q') : '' }}" placeholder="Search the catalog…" class="store-input !mt-0 !py-2">
                </form>

                <div class="ml-auto flex items-center gap-1 sm:gap-2">
                    <a href="{{ route('cart.show') }}" class="store-nav-link inline-flex items-center gap-2">
                        <span>Cart</span>
                        @if (($cartItemCount ?? 0) > 0)
                            <span class="store-cart-badge" aria-label="{{ $cartItemCount }} items">{{ $cartItemCount }}</span>
                        @endif
                    </a>
                    @auth
                        <a href="{{ route('orders.index') }}" class="store-nav-link hidden sm:inline-flex">Orders</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.orders.index') }}" class="store-nav-link hidden xl:inline-flex">Admin</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                            @csrf
                            <button type="submit" class="store-nav-link">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="store-nav-link hidden sm:inline-flex">Log in</a>
                        <a href="{{ route('register') }}" class="store-nav-cta">Register</a>
                    @endauth
                    <button type="button" class="store-nav-menu-btn" data-mobile-nav-toggle aria-expanded="false" aria-controls="mobile-nav">
                        Menu
                    </button>
                </div>
            </div>
        </div>
        <div id="mobile-nav" class="hidden border-t border-line bg-surface lg:hidden" data-mobile-nav>
            <div class="store-shell space-y-1 py-4">
                <form method="GET" action="{{ route('shop.index') }}" class="mb-3" role="search">
                    <label for="mobile-search" class="store-label">Search</label>
                    <input id="mobile-search" name="q" type="search" class="store-input" placeholder="Search products">
                </form>
                <a href="{{ route('shop.index') }}" class="store-mobile-nav-link">Shop</a>
                <a href="{{ route('cart.show') }}" class="store-mobile-nav-link">Cart</a>
                @auth
                    <a href="{{ route('orders.index') }}" class="store-mobile-nav-link">Orders</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="store-mobile-nav-link w-full text-left">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="store-mobile-nav-link">Log in</a>
                    <a href="{{ route('register') }}" class="store-mobile-nav-link">Register</a>
                @endauth
            </div>
        </div>
    </header>

    @hasSection('hero')
        @yield('hero')
    @endif

    <main class="@yield('mainClass', 'store-shell store-main')">
        @if (session('success'))
            <p class="store-flash-success flash-enter mb-8" role="status">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="store-flash-error flash-enter mb-8" role="alert">{{ session('error') }}</p>
        @endif
        @yield('content')
    </main>

    <footer class="store-footer">
        <div class="store-shell py-12 lg:py-14">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2 lg:col-span-1">
                    <p class="font-display text-lg font-semibold text-ink">{{ config('app.name') }}</p>
                    <p class="mt-3 max-w-xs text-sm leading-relaxed text-muted">Premium essentials for modern living. Thoughtfully curated, securely delivered.</p>
                </div>
                <div>
                    <p class="store-label !text-ink/70">Shop</p>
                    <ul class="mt-3 space-y-2">
                        <li><a href="{{ route('shop.index') }}" class="store-footer-link">All products</a></li>
                        <li><a href="{{ route('home') }}#categories" class="store-footer-link">Categories</a></li>
                        <li><a href="{{ route('cart.show') }}" class="store-footer-link">Cart</a></li>
                    </ul>
                </div>
                <div>
                    <p class="store-label !text-ink/70">Account</p>
                    <ul class="mt-3 space-y-2">
                        @auth
                            <li><a href="{{ route('orders.index') }}" class="store-footer-link">Orders</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="store-footer-link">Log in</a></li>
                            <li><a href="{{ route('register') }}" class="store-footer-link">Register</a></li>
                        @endauth
                    </ul>
                </div>
                <div>
                    <p class="store-label !text-ink/70">Service</p>
                    <ul class="mt-3 space-y-2 text-sm text-muted">
                        <li>Secure checkout</li>
                        <li>Reserved inventory</li>
                        <li>Order tracking</li>
                    </ul>
                </div>
            </div>
            <p class="mt-10 border-t border-line pt-6 text-xs text-muted">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </footer>
</body>
</html>
