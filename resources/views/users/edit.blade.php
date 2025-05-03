@extends('layouts.app')

@section('title', 'Editar Usuário - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Editar Usuário')

@section('breadcrumb')
<a href="{{ route('users.index') }}">Usuários</a> / <span>Editar</span>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3>Editar Informações do Usuário</h3>
    </div>
    <div class="panel-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('users.update', $usuario->id_usuario) }}" method="POST" class="form-usuario">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="nome">Nome completo <span class="required">*</span></label>
                <input type="text" id="nome" name="nome" value="{{ old('nome', $usuario->nome) }}" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-mail <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="tel" id="telefone" name="telefone" value="{{ old('telefone', $usuario->telefone) }}" class="form-control" placeholder="(00) 00000-0000">
            </div>
            
            <div class="form-group">
                <label for="max_emprestimos">Máximo de Livros Permitidos</label>
                <input type="number" id="max_emprestimos" name="max_emprestimos" value="{{ old('max_emprestimos', $usuario->max_emprestimos) }}" class="form-control" min="1" max="{{ $settings['max_loans_per_user'] ?? 3 }}">
                <small class="form-text text-muted">Máximo definido nas configurações: {{ $settings['max_loans_per_user'] ?? 3 }} livros</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Atualizar
                </button>
                <a href="{{ route('users.index', $usuario->id_usuario) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
    @vite(['resources/css/usuario/usuario.css'])
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Máscara para o campo de telefone
        const telefoneInput = document.getElementById('telefone');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.slice(0, 11);
                
                if (value.length > 2) {
                    value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
                }
                if (value.length > 10) {
                    value = value.substring(0, 10) + '-' + value.substring(10);
                }
                e.target.value = value;
            });
        }
    });
</script>
@endpush