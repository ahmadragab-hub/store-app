@extends('layouts.app')

@section('title', 'Verify email')

@section('content')
    <section class="w-full max-w-md">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-accent">One more step</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Verify your email</h1>
            <p class="mt-2 text-sm text-stone-600">
                A verification link was written to <span class="font-mono">storage/logs/laravel.log</span> (mail is in log mode on this computer). Open that link, or send a new one.
            </p>

            <form method="POST" action="{{ route('verification.send') }}" class="mt-8">
                @csrf
                <button type="submit" class="store-button">Resend verification email</button>
            </form>
        </div>
    </section>
@endsection
