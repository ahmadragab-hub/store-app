@extends('layouts.app')

@section('title', 'Reset password')

@section('content')
    <div class="store-auth-wrap">
        <div class="store-auth-card">
            <p class="store-eyebrow">Account</p>
            <h1 class="font-display mt-2 text-2xl font-semibold text-ink">Choose a new password</h1>

            <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label for="email" class="store-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required class="store-input">
                    @error('email')<p class="store-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="store-label">New password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password" class="store-input">
                    @error('password')<p class="store-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="store-label">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="store-input">
                </div>
                <button type="submit" class="store-button">Update password</button>
            </form>
        </div>
    </div>
@endsection
