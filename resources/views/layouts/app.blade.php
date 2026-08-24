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
        <nav class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <a href="{{ url('/') }}" class="text-lg font-semibold tracking-tight text-stone-900">
                {{ config('app.name') }}
            </a>

            <div class="flex items-center gap-3 text-sm">
                @auth
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

    <main class="mx-auto flex w-full max-w-5xl flex-1 justify-center px-4 py-12 sm:px-6 sm:py-16">
        @yield('content')
    </main>
</body>
</html>
