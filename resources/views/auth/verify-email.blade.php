@extends('layouts.app')

@section('title', 'Verify email')

@section('content')
    <div class="store-auth-wrap">
        <div class="store-auth-card">
            <p class="store-eyebrow">Verification</p>
            <h1 class="font-display mt-2 text-2xl font-semibold text-ink">Confirm your email</h1>
            <p class="mt-2 text-sm text-muted">
                We sent a verification link to your inbox. On this dev server, check <span class="font-mono text-xs">storage/logs/laravel.log</span> or request a new link below.
            </p>

            <form method="POST" action="{{ route('verification.send') }}" class="mt-8">
                @csrf
                <button type="submit" class="store-button">Resend verification email</button>
            </form>
        </div>
    </div>
@endsection
