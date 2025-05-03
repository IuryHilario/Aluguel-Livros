@extends('layouts.app')

@section('title', 'Meu Perfil - '  . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Meu Perfil')

@vite(['resources/css/profile/profile.css'])

@section('breadcrumb')
<span>Meu Perfil</span>
@endsection

@section('content')
<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <span>{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
        </div>
        <div class="profile-welcome">
            <h2>Olá, {{ explode(' ', Auth::user()->name)[0] }}!</h2>
            <p>Gerencie suas informações pessoais e senha aqui</p>
        </div>
    </div>

    <div class="profile-tabs">
        <button class="tab-button active" data-tab="personal-info">Informações Pessoais</button>
        <button class="tab-button" data-tab="security">Segurança</button>
    </div>

    <div class="profile-content">
        <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
            @csrf
            @method('PATCH')
            
            <div class="tab-content active" id="personal-info-content">
                <div class="panel">
                    <div class="panel-header">
                        <h3>Informações Pessoais</h3>
                        <p>Atualize suas informações básicas</p>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="name">Nome Completo</label>
                            <input type="text" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required autofocus />
                            @error('name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required />
                            @error('email')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="security-content">
                <div class="panel">
                    <div class="panel-header">
                        <h3>Alterar Senha</h3>
                        <p>Mantenha sua conta segura atualizando sua senha regularmente</p>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="current_password">Senha Atual</label>
                            <div class="password-input-container">
                                <input type="password" id="current_password" name="current_password" />
                                <button type="button" class="password-toggle">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Nova Senha</label>
                            <div class="password-input-container">
                                <input type="password" id="password" name="password" />
                                <button type="button" class="password-toggle">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Nova Senha</label>
                            <div class="password-input-container">
                                <input type="password" id="password_confirmation" name="password_confirmation" />
                                <button type="button" class="password-toggle">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

@vite(['resources/js/profile.js'])
@endsection