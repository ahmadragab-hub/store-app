@extends('layouts.app')

@section('title', 'Forgot password')

@section('content')
    <section class="w-full max-w-md">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-accent">Account</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Forgot password</h1>
            <p class="mt-2 text-sm text-stone-600">We will write a reset link to the mail log if that email exists.</p>

            <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="store-label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="store-input">
                    @error('email')
                        <p class="store-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="store-button">Send reset link</button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-stone-600">
            <a href="{{ route('login') }}" class="font-semibold text-stone-900 underline decoration-stone-300 underline-offset-4 hover:decoration-stone-900">Back to login</a>
        </p>
    </section>
@endsection
