@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <div class="store-auth-wrap">
        <div class="store-auth-card">
            <p class="store-eyebrow">Account</p>
            <h1 class="font-display mt-2 text-2xl font-semibold text-ink">Welcome back</h1>
            <p class="mt-2 text-sm text-muted">Sign in to checkout, track orders, and manage your profile.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="store-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="store-input">
                    @error('email')<p class="store-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="store-label">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="store-input">
                    @error('password')<p class="store-error">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="store-button">Log in</button>
            </form>
        </div>

        @include('storefront._demo-panel')

        <p class="mt-6 text-center text-sm text-muted">
            <a href="{{ route('password.request') }}" class="store-link">Forgot password?</a>
        </p>
        <p class="mt-4 text-center text-sm text-muted">
            New here? <a href="{{ route('register') }}" class="store-link">Create account</a>
        </p>
    </div>
@endsection
