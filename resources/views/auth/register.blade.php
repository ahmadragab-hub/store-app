@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <div class="store-auth-wrap">
        <div class="store-auth-card">
            <p class="store-eyebrow">Join</p>
            <h1 class="font-display mt-2 text-2xl font-semibold text-ink">Create your account</h1>
            <p class="mt-2 text-sm text-muted">Register to checkout faster and view order history.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="name" class="store-label">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" class="store-input">
                    @error('name')<p class="store-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="store-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="store-input">
                    @error('email')<p class="store-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="store-label">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password" class="store-input">
                    @error('password')<p class="store-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="store-label">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="store-input">
                </div>
                <button type="submit" class="store-button">Create account</button>
            </form>
        </div>
        <p class="mt-6 text-center text-sm text-muted">
            Already registered? <a href="{{ route('login') }}" class="store-link">Log in</a>
        </p>
    </div>
@endsection
