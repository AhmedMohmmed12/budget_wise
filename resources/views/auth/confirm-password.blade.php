@extends('layouts.auth')

@section('title', 'Confirm password')
@section('heading', 'Quick check')
@section('subheading', 'Enter your password again before we carry on.')

@section('content')
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <label for="password" class="label">Password</label>
            <input type="password" id="password" name="password"
                   class="field" autocomplete="current-password" required autofocus>
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-pine w-full">Confirm</button>
    </form>
@endsection
