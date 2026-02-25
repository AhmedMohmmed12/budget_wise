<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name', 'BudgetWise') }}</title>

    <!-- Preload Critical Resources -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    
    <!-- Bootstrap 5 - Async loaded -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></noscript>
    
    <!-- Font Awesome - Async loaded -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    
    <style>
        @font-face {
            font-family: 'Font Awesome 6 Free';
            font-display: swap;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Fixed Background Elements */
        .auth-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }

        .auth-bg::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px),
                radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            background-position: 0 0, 25px 25px;
            animation: moveBackground 20s linear infinite;
        }

        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        /* Floating Shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 15s infinite ease-in-out;
        }

        .shape1 { width: 350px; height: 350px; background: linear-gradient(45deg, #667eea, #764ba2); top: 5%; left: 5%; animation-delay: 0s; }
        .shape2 { width: 250px; height: 250px; background: linear-gradient(135deg, #f093fb, #f5576c); bottom: 10%; right: 10%; animation-delay: 2s; }
        .shape3 { width: 180px; height: 180px; background: linear-gradient(225deg, #4facfe, #00f2fe); top: 50%; left: 75%; animation-delay: 4s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-30px) rotate(90deg); }
            50% { transform: translateY(-60px) rotate(180deg); }
            75% { transform: translateY(-30px) rotate(270deg); }
        }

        .auth-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
            padding: 40px 20px;
            z-index: 1;
            position: relative;
        }

        .auth-container {
            width: 100%;
            max-width: @yield('max_width', '480px');
            margin: auto;
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo { text-align: center; margin-bottom: 40px; animation: bounceIn 1s ease; }
        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }

        .logo-icon {
            width: 80px; height: 80px; margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4); }
            50% { transform: scale(1.05); box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6); }
        }

        .logo-icon i { font-size: 40px; color: white; }
        .logo h1 {
            font-size: 32px; font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 10px;
        }
        .logo p { color: #64748b; font-size: 14px; font-weight: 500; }

        .form-group { margin-bottom: 25px; position: relative; }
        .form-group label { display: block; margin-bottom: 8px; color: #334155; font-weight: 600; font-size: 14px; }
        .input-group-custom { position: relative; }
        .input-group-custom i {
            position: absolute; left: 18px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 16px; transition: all 0.3s ease;
        }

        .form-control {
            width: 100%; padding: 14px 20px 14px 50px;
            border: 2px solid #e2e8f0; border-radius: 15px;
            font-size: 15px; transition: all 0.3s ease; background: white;
        }
        .form-control:focus {
            outline: none; border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        .form-control:focus + .input-group-custom i { color: #667eea; }

        .invalid-feedback { color: #ef4444; font-size: 13px; margin-top: 5px; }
        .btn-auth {
            width: 100%; padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border: none; border-radius: 15px;
            font-size: 16px; font-weight: 700; cursor: pointer;
            transition: all 0.3s ease; box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            position: relative; overflow: hidden;
        }
        .btn-auth:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(102, 126, 234, 0.6); }

        .auth-link { text-align: center; margin-top: 25px; color: #64748b; font-size: 14px; }
        .auth-link a { color: #667eea; text-decoration: none; font-weight: 700; transition: all 0.3s ease; }
        .auth-link a:hover { color: #764ba2; text-decoration: underline; }

        @media (max-width: 576px) {
            .auth-card { padding: 40px 25px; }
            .logo h1 { font-size: 28px; }
        }
    </style>
</head>
<body>
    <div class="auth-bg">
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
    </div>

    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-card">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas @yield('logo_icon', 'fa-wallet')"></i>
                    </div>
                    <h1>@yield('logo_heading', 'BudgetWise')</h1>
                    <p>@yield('logo_subheading')</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-info border-0 rounded-4 mb-4">
                        <i class="fas fa-info-circle me-2"></i>{{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
