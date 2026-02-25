@extends('layouts.auth')

@section('title', 'Register')
@section('max_width', '520px')
@section('logo_icon', 'fa-user-plus')
@section('logo_heading', 'Create Account')
@section('logo_subheading', 'Join BudgetWise and take control of your finances')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="form-group">
            <label for="name">Full Name</label>
            <div class="input-group-custom">
                <i class="fas fa-user"></i>
                <input 
                    type="text" 
                    class="form-control @error('name') is-invalid @enderror" 
                    id="name" 
                    name="name" 
                    value="{{ old('name') }}" 
                    placeholder="Enter your full name"
                    required 
                    autofocus>
            </div>
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Username -->
        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-group-custom">
                <i class="fas fa-at"></i>
                <input 
                    type="text" 
                    class="form-control @error('username') is-invalid @enderror" 
                    id="username" 
                    name="username" 
                    value="{{ old('username') }}" 
                    placeholder="Choose a unique username"
                    required>
            </div>
            @error('username')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email -->
        <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-group-custom">
                <i class="fas fa-envelope"></i>
                <input 
                    type="email" 
                    class="form-control @error('email') is-invalid @enderror" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    placeholder="Enter your email address"
                    required>
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
                    placeholder="Create a strong password"
                    required>
            </div>
            <div style="margin-top: 8px; font-size: 12px; color: #64748b;">
                <i class="fas fa-info-circle me-1"></i>
                Minimum 8 characters recommended
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <div class="input-group-custom">
                <i class="fas fa-lock-open"></i>
                <input 
                    type="password"
                    class="form-control" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    placeholder="Re-enter your password"
                    required>
            </div>
        </div>

        <button type="submit" class="btn-auth">
            <i class="fas fa-rocket me-2"></i>Create My Account
        </button>

        <div class="auth-link">
            Already have an account? 
            <a href="{{ route('login') }}">
                <i class="fas fa-sign-in-alt me-1"></i>Sign In
            </a>
        </div>
    </form>
@endsection
