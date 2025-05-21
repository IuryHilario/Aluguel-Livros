@extends('layouts.app')

@section('title', 'Usuários - ' . $settings['system_name'] ?? 'Aluga Livros')

@section('page-title', 'Gerenciar Usuários')

@section('breadcrumb')
<a href="{{ route('users.index') }}">Usuários</a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3><i class="fas fa-users"></i> Lista de Usuários</h3>
        <div class="panel-actions">
            <button class="btn-icon" id="toggleFilter"><i class="fas fa-filter"></i></button>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Novo Usuário
            </a>
        </div>
    </div>
    <div class="panel-body">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="filter-container" style="{{ (request('search') || request('order_by') || request('order_dir')) ? 'display: block;' : 'display: none;' }}">
            <form action="{{ route('users.index') }}" method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="filter-column">
                        <label for="search">Pesquisar</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nome, email ou telefone" autocomplete="off">
                    </div>
                    <div class="filter-column">
                        <label for="order_by">Ordenar por</label>
                        <select id="order_by" name="order_by" class="form-control">
                            <option value="nome" {{ request('order_by') == 'nome' || !request('order_by') ? 'selected' : '' }}>Nome</option>
                            <option value="email" {{ request('order_by') == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="created_at" {{ request('order_by') == 'created_at' ? 'selected' : '' }}>Data de cadastro</option>
                        </select>
                    </div>
                    <div class="filter-column">
                        <label for="order_dir">Direção</label>
                        <select id="order_dir" name="order_dir" class="form-control">
                            <option value="asc" {{ request('order_dir') == 'asc' || !request('order_dir') ? 'selected' : '' }}>Crescente</option>
                            <option value="desc" {{ request('order_dir') == 'desc' ? 'selected' : '' }}>Decrescente</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Limpar</a>
                    </div>
                </div>
            </form>
        </div>

        @if(request('search'))
            <div class="filter-active">
                <p>
                    <span class="filter-tag">
                        <i class="fas fa-search"></i> Buscando: <strong>{{ request('search') }}</strong>
                    </span>
                </p>
            </div>
        @endif

        <div class="table-responsive">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Cadastrado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->id_usuario }}</td>
                            <td>
                                <div class="user-info-cell">
                                    <span class="user-avatar-small">{{ strtoupper(substr($usuario->nome, 0, 1)) }}</span>
                                    <span>{{ $usuario->nome }}</span>
                                </div>
                            </td>
                            <td>{{ $usuario->email }}</td>
                            <td>{{ $usuario->telefone ?? 'Não informado' }}</td>
                            <td>{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : 'N/A' }}</td>
                            <td class="actions">
                                <x-form.actions
                                    show="{{ route('users.show', $usuario->id_usuario) }}"
                                    edit="{{ route('users.edit', $usuario->id_usuario) }}"
                                    delete="{{ route('users.destroy', $usuario->id_usuario) }}"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h4>Nenhum usuário encontrado</h4>
                                <p>Não existem usuários cadastrados com os critérios especificados.</p>
                                @if(request('search') || request('order_by') || request('order_dir'))
                                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-undo"></i> Limpar Filtros
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usuarios->count() > 0)
            <div class="pagination-container">
                {{ $usuarios->appends(request()->query())->links('components.pagination') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
    @vite(['resources/css/usuario/usuario.css'])
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleFilter = document.getElementById('toggleFilter');
        const filterContainer = document.querySelector('.filter-container');

        // Verificar se há filtros aplicados
        const urlParams = new URLSearchParams(window.location.search);
        const hasFilters = urlParams.has('search') || urlParams.has('order_by') || urlParams.has('order_dir');

        // Mostrar filtros automaticamente se houver algum aplicado
        if (hasFilters) {
            filterContainer.style.display = 'block';
        }

        toggleFilter.addEventListener('click', function() {
            filterContainer.style.display = filterContainer.style.display === 'none' ? 'block' : 'none';
        });
    });
</script>
@endpush
