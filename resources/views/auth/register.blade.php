@extends('layouts.auth')

@section('title', 'Sign up')
@section('heading', 'Start your book')
@section('subheading', 'Takes about a minute. No card, no trial, nothing to cancel.')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="label">Your name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   class="field" placeholder="Sara Ali" autocomplete="name" required autofocus>
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="username" class="label">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                       class="field" placeholder="sara" required>
                @error('username')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="field" placeholder="sara@example.com" autocomplete="email" required>
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="label">Password</label>
                <input type="password" id="password" name="password"
                       class="field" autocomplete="new-password" required>
                <p class="mt-1 text-xs text-ink-soft">8 characters or more.</p>
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Same again</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="field" autocomplete="new-password" required>
            </div>
        </div>

        <button type="submit" class="btn-pine w-full">Create my book</button>

        <p class="text-center text-sm text-ink-soft">
            Already have one? <a href="{{ route('login') }}" class="link text-ink">Log in</a>
        </p>
    </form>
@endsection
