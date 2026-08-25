<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper font-sans text-ink antialiased">
    <header class="border-b border-stone-200/80 bg-white/80 backdrop-blur">
        <nav class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6">
            <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight text-stone-900">
                {{ config('app.name') }}
            </a>

            <div class="flex flex-wrap items-center gap-2 text-sm sm:gap-3">
                <a href="{{ route('shop.index') }}" class="rounded-lg px-3 py-1.5 font-medium text-stone-700 transition hover:bg-stone-100">
                    Shop
                </a>
                @auth
                    <a href="{{ route('cart.show') }}" class="rounded-lg px-3 py-1.5 font-medium text-stone-700 transition hover:bg-stone-100">
                        Cart
                    </a>
                    <a href="{{ route('orders.index') }}" class="rounded-lg px-3 py-1.5 font-medium text-stone-700 transition hover:bg-stone-100">
                        Orders
                    </a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('categories.index') }}" class="rounded-lg px-3 py-1.5 font-medium text-stone-700 transition hover:bg-stone-100">
                            Categories
                        </a>
                        <a href="{{ route('products.index') }}" class="rounded-lg px-3 py-1.5 font-medium text-stone-700 transition hover:bg-stone-100">
                            Products
                        </a>
                    @endif
                    <span class="hidden text-stone-600 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-1.5 font-medium text-stone-700 transition hover:bg-stone-100">
                            Log out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-1.5 font-medium text-stone-700 transition hover:bg-stone-100">
                        Log in
                    </a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-stone-900 px-3 py-1.5 font-semibold text-white transition hover:bg-stone-800">
                        Register
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="mx-auto flex w-full max-w-5xl flex-1 flex-col px-4 py-12 sm:px-6 sm:py-16">
        @if (session('success'))
            <p class="store-flash-success mb-6">{{ session('success') }}</p>
        @endif

        @if (session('error'))
            <p class="store-flash-error mb-6">{{ session('error') }}</p>
        @endif

        <div class="flex flex-1 justify-center">
            @yield('content')
        </div>
    </main>
</body>
</html>
