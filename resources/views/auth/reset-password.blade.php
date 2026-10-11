@extends('layouts.auth')

@section('title', 'New password')
@section('heading', 'Pick a new password')

@section('content')
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="label">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}"
                   class="field" autocomplete="username" required autofocus>
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="label">New password</label>
            <input type="password" id="password" name="password"
                   class="field" autocomplete="new-password" required>
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">Same again</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="field" autocomplete="new-password" required>
            @error('password_confirmation')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-pine w-full">Save new password</button>
    </form>
@endsection
