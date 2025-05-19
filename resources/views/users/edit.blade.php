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

            <x-form.input
                label="Nome Completo"
                type="text"
                name="nome"
                id="nome"
                value="{{ $usuario->nome }}"
                required
            />

            <x-form.input
                label="E-mail"
                type="email"
                name="email"
                id="email"
                value="{{ $usuario->email }}"
                required
            />

            <x-form.input
                label="Telefone"
                type="tel"
                name="telefone"
                id="telefone"
                value="{{ $usuario->telefone }}"
                placeholder="(00) 00000-0000"
            />

            <x-form.input
                label="Máximo de Livros Permitidos"
                placeholder="Digite o máximo de livros permitidos"
                type="number"
                name="max_emprestimos"
                id="max_emprestimos"
                value="{{ old('max_emprestimos', $settings['max_loans_per_user'] ?? 3) }}"
                min="1"
                max="{{ $settings['max_loans_per_user'] ?? 3 }}"
                required
            />

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
