@extends('layouts.auth')

@section('title', 'Login')
@section('logo_subheading', 'Welcome back! Please login to your account')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email/Username -->
        <div class="form-group">
            <label for="email">Email or Username</label>
            <div class="input-group-custom">
                <i class="fas fa-user"></i>
                <input 
                    type="text" 
                    class="form-control @error('email') is-invalid @enderror" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    placeholder="Enter your email or username"
                    required 
                    autofocus>
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-group-custom">
                <i class="fas fa-lock"></i>
                <input 
                    type="password"
                    class="form-control @error('password') is-invalid @enderror" 
                    id="password" 
                    name="password"
                    placeholder="Enter your password"
                    required>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                <label class="form-check-label" for="remember_me" style="color: #64748b; font-size: 14px;">
                    Remember me
                </label>
            </div>
            @if (Route::has('password.request'))
                <div>
                    <a href="{{ route('password.request') }}" class="text-decoration-none" style="color: #667eea; font-size: 13px; font-weight: 600;">
                        <i class="fas fa-key me-1"></i>Forgot password?
                    </a>
                </div>
            @endif
        </div>

        <button type="submit" class="btn-auth">
            <i class="fas fa-sign-in-alt me-2"></i>Login to Account
        </button>

        <div class="auth-link">
            Don't have an account? 
            <a href="{{ route('register') }}">
                <i class="fas fa-user-plus me-1"></i>Create Account
            </a>
        </div>
    </form>
@endsection
