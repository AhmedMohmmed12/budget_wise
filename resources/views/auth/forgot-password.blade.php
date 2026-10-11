@extends('layouts.auth')

@section('title', 'Forgot password')
@section('heading', 'Forgot your password?')
@section('subheading', "Tell us your email and we'll send a link to pick a new one.")

@section('content')
    @if (session('status'))
        <div class="note-pine mb-5">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="label">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="field" placeholder="you@example.com" required autofocus>
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-pine w-full">Send me the link</button>

        <p class="text-center text-sm text-ink-soft">
            Remembered it? <a href="{{ route('login') }}" class="link text-ink">Back to log in</a>
        </p>
    </form>
@endsection
