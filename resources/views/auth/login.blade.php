@extends('layouts.auth')

@section('title', 'Log in')
@section('heading', 'Welcome back')

@section('content')
    @if (session('status'))
        <div class="note-pine mb-5">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="label">Email or username</label>
            <input type="text" id="email" name="email" value="{{ old('email') }}"
                   class="field" placeholder="you@example.com" autocomplete="username" required autofocus>
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input type="password" id="password" name="password"
                   class="field" autocomplete="current-password" required>
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between text-[13px] text-ink-soft">
            <label for="remember_me" class="flex items-center gap-2">
                <input type="checkbox" id="remember_me" name="remember"
                       class="rounded border-ink text-pine focus:ring-pine">
                Keep me signed in
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="link">Forgot it?</a>
            @endif
        </div>

        <button type="submit" class="btn-pine w-full">Open my book</button>

        <p class="text-center text-sm text-ink-soft">
            New here? <a href="{{ route('register') }}" class="link text-ink">Start a book</a>
        </p>
    </form>
@endsection
