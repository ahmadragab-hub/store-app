@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <section class="w-full max-w-md">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-accent">Welcome back</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Log in to your account</h1>
            <p class="mt-2 text-sm text-stone-600">Use the same email you registered with.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="store-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="store-input">
                    @error('email')
                        <p class="store-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="store-label">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="store-input">
                    @error('password')
                        <p class="store-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="store-button">Log in</button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-stone-600">
            <a href="{{ route('password.request') }}" class="font-semibold text-stone-900 underline decoration-stone-300 underline-offset-4 hover:decoration-stone-900">Forgot password?</a>
        </p>

        <p class="mt-6 text-center text-sm text-stone-600">
            Need an account?
            <a href="{{ route('register') }}" class="font-semibold text-stone-900 underline decoration-stone-300 underline-offset-4 hover:decoration-stone-900">Register</a>
        </p>
    </section>
@endsection
