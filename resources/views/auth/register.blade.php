<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            <form action="{{ route('register.post') }}" method="POST">
                @csrf
                <div class="inputBox">
                    <label><i class="fas fa-user"></i> Nome</label>
                    <input type="text" name="name" placeholder="Digite seu nome" required>
                </div>
                <div class="inputBox">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" placeholder="Digite seu email" required>
                </div>
                <div class="inputBox">
                    <label><i class="fas fa-lock"></i> Senha</label>
                    <input type="password" name="password" placeholder="Digite sua senha" required>
                </div>
                <div class="inputBox">
                    <label><i class="fas fa-lock"></i> Confirmar Senha</label>
                    <input type="password" name="password_confirmation" placeholder="Confirme sua senha" required>
                </div>
                <button type="submit">Registrar <i class="fas fa-arrow-right"></i></button>
            </form>
            @if ($errors->any())
                <div class="error-messages">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="register-section">
                <p>Já tem uma conta? <a href="{{ route('login') }}">Login</a></p>
            </div>
        </div>
    </div>
</body>
</html>