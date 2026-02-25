<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="BudgetWise - Track your expenses, set budgets, and achieve your financial goals with our easy-to-use budget tracking application.">
        <meta name="theme-color" content="#6366f1">
        
        <!-- Performance Hints -->
        <meta http-equiv="x-dns-prefetch-control" content="on">

    <title>{{ config('app.name', 'BudgetWise') }} - @yield('title', 'Dashboard')</title>

    <!-- Preconnect to CDN domains for faster loading -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

    <!-- Bootstrap 5 CSS - Preloaded -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></noscript>
    
    <!-- Font Awesome 6 - Async loaded with font-display swap -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    
    <!-- CSS Loader Fallback Script -->
    <script>
        !function(e){"use strict";var t=function(t,n,o,i){var r,a=e.document,d=a.createElement("link");if(n)r=n;else{var l=(a.body||a.getElementsByTagName("head")[0]).childNodes;r=l[l.length-1]}var s=a.styleSheets;if(i)for(var f in i)i.hasOwnProperty(f)&&d.setAttribute(f,i[f]);d.rel="stylesheet",d.href=t,d.media="only x",function e(t){if(a.body)return t();setTimeout(function(){e(t)})}(function(){r.parentNode.insertBefore(d,n?r:r.nextSibling)});var u=function(e){for(var t=d.href,n=s.length;n--;)if(s[n].href===t)return e();setTimeout(function(){u(e)})};return d.addEventListener&&d.addEventListener("load",o),d.onloadcssdefined=u,u(o),d};"undefined"!=typeof exports?exports.loadCSS=t:e.loadCSS=t}("undefined"!=typeof global?global:this);
    </script>

    <!-- Custom CSS -->
    <style>
        /* Critical CSS - Inlined for faster first paint */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: #333;
            min-height: 100vh;
        }

        /* Font Display Swap for better performance */
        @font-face {
            font-family: 'Font Awesome 6 Free';
            font-display: swap;
        }

        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary-color: #10b981;
            --secondary-dark: #059669;
            --accent-color: #f59e0b;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --neutral-50: #fafafa;
            --neutral-100: #f5f5f5;
            --neutral-800: #262626;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        * {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3), transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(167, 139, 250, 0.3), transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        main {
            position: relative;
            z-index: 1;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .nav-link {
            position: relative;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%) scaleX(0);
            width: 80%;
            height: 2px;
            background: var(--gradient-primary);
            transition: transform 0.3s ease;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            transform: translateX(-50%) scaleX(1);
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            font-weight: 600;
            padding: 0.625rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .card {
            border: none;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-bottom: 1px solid rgba(102, 126, 234, 0.2);
        }

        .badge-category {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 50px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .progress {
            height: 10px;
            border-radius: 10px;
            background: rgba(102, 126, 234, 0.1);
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            animation: progressSlide 1.5s ease;
        }

        @keyframes progressSlide {
            from { width: 0; }
        }

        .alert {
            border-radius: 15px;
            border: none;
            animation: slideInRight 0.5s ease;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .table-hover tbody tr {
            transition: all 0.3s ease;
        }

        .table-hover tbody tr:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        /* Mobile Navigation */
        @media (max-width: 768px) {
            .mobile-nav {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                box-shadow: 0 -4px 30px rgba(0, 0, 0, 0.1);
                z-index: 1000;
                border-top: 1px solid rgba(102, 126, 234, 0.2);
            }

            .mobile-nav .nav-link {
                padding: 0.75rem;
                text-align: center;
                font-size: 0.875rem;
                position: relative;
            }

            .mobile-nav .nav-link i {
                display: block;
                font-size: 1.5rem;
                margin-bottom: 0.25rem;
                transition: all 0.3s ease;
            }

            .mobile-nav .nav-link:hover i,
            .mobile-nav .nav-link.text-primary i {
                transform: scale(1.2) rotate(5deg);
            }

            main {
                padding-bottom: 80px !important;
            }
        }

        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Glassmorphism */
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* Loading Animation */
        .spinner-border {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }
    </style>

    @stack('styles')
    </head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-wallet text-primary me-2"></i>
                BudgetWise
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-bold' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('budgets.index') ? 'active fw-bold' : '' }}" href="{{ route('budgets.index') }}">
                            <i class="fas fa-chart-pie me-1"></i> Set Budget
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('expense-tips') ? 'active fw-bold' : '' }}" href="{{ route('expense-tips') }}">
                            <i class="fas fa-lightbulb me-1"></i> Tips
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name ?? 'User' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4">
        @if(session('success'))
            <div class="container">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                    </div>
        @endif

        @yield('content')
            </main>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav d-md-none">
        <div class="container-fluid">
            <div class="row text-center">
                <div class="col">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'text-primary' : 'text-secondary' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i>
                        <div>Home</div>
                    </a>
                </div>
                <div class="col">
                    <a class="nav-link {{ request()->routeIs('budgets.index') ? 'text-primary' : 'text-secondary' }}" href="{{ route('budgets.index') }}">
                        <i class="fas fa-chart-pie"></i>
                        <div>Budget</div>
                    </a>
                </div>
                <div class="col">
                    <a class="nav-link {{ request()->routeIs('expense-tips') ? 'text-primary' : 'text-secondary' }}" href="{{ route('expense-tips') }}">
                        <i class="fas fa-lightbulb"></i>
                        <div>Tips</div>
                    </a>
                </div>
                <div class="col">
                    <a class="nav-link {{ request()->routeIs('profile.index') ? 'text-primary' : 'text-secondary' }}" href="{{ route('profile.index') }}">
                        <i class="fas fa-user"></i>
                        <div>Profile</div>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS - Deferred -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    
    <!-- jQuery (for AJAX) - Deferred -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" defer></script>

    @stack('scripts')
    </body>
</html>
