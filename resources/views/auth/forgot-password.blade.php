@extends('layouts.app')

@section('title', 'Forgot password')

@section('content')
    <div class="store-auth-wrap">
        <div class="store-auth-card">
            <p class="store-eyebrow">Account</p>
            <h1 class="font-display mt-2 text-2xl font-semibold text-ink">Reset password</h1>
            <p class="mt-2 text-sm text-muted">If your email exists, we will send a reset link (logged locally in dev).</p>

            <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="store-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="store-input">
                    @error('email')<p class="store-error">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="store-button">Send reset link</button>
            </form>
        </div>
        <p class="mt-6 text-center text-sm text-muted">
            <a href="{{ route('login') }}" class="store-link">Back to login</a>
        </p>
    </div>
@endsection
