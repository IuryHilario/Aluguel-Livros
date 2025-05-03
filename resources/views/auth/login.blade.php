<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/login.css'])
</head>
<body>
    <div class="container">
        <div class="boxLogin">
            <div class="logo-section">
                <div class="logo-circle">
                    <i class="fas fa-book"></i>
                </div>
                <h1>{{ $settings['system_name'] ?? 'Aluga Livros' }}</h1>
            </div>
            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="inputBox">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="text" name="email" placeholder="Enter your email" required>
                </div>
                <div class="inputBox">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <br>
                <button type="submit">Login <i class="fas fa-arrow-right"></i></button>
            </form>
            @if (session('error'))
                <p class="error-message">{{ session('error') }}</p>
            @endif
            
            <div class="register-section">
                <p>Don't have an account? <a href="{{ route('register') }}">Register</a></p>
            </div>
        </div>
    </div>
</body>
</html>