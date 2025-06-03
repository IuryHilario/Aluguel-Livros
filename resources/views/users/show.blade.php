@extends('layouts.app')

@section('title', 'Detalhes do Usuário - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Detalhes do Usuário')

@section('breadcrumb')
<a href="{{ route('users.index') }}">Usuários</a> / <span>Detalhes</span>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3>Informações do Usuário</h3>
        <div class="panel-actions">
            <a href="{{ route('users.edit', $usuario->id_usuario) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>
    <div class="panel-body">
        <div class="user-details">
            <div class="user-header">
                <div class="user-avatar">
                    <span>{{ strtoupper(substr($usuario->nome, 0, 1)) }}</span>
                </div>
                <div class="user-header-info">
                    <h2 class="user-name">{{ $usuario->nome }}</h2>
                    <p class="user-email"><i class="fas fa-envelope"></i> {{ $usuario->email }}</p>
                </div>
            </div>

            <div class="user-stats">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-book"></i></div>
                    <div class="stat-content">
                        <span class="stat-value">{{ count($usuario->alugueis->where('ds_status', '!=', 'Devolvido')) }}</span>
                        <span class="stat-label">Livros Ativos</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
                    <div class="stat-content">
                        <span class="stat-value">{{ count($usuario->alugueis) }}</span>
                        <span class="stat-label">Total de Aluguéis</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-content">
                        <span class="stat-value">{{ count($usuario->alugueis->where('ds_status', 'Atrasado')) }}</span>
                        <span class="stat-label">Atrasos</span>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <h3><i class="fas fa-user-tag"></i> Informações Pessoais</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-phone"></i> Telefone:</div>
                        <div class="info-value">{{ $usuario->telefone ?? 'Não informado' }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-layer-group"></i> Máximo de Livros:</div>
                        <div class="info-value">{{ $usuario->max_emprestimos ?? ($settings['max_loans_per_user'] ?? 3) }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-calendar-plus"></i> Data de Cadastro:</div>
                        <div class="info-value">{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i') : 'N/A' }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-calendar-check"></i> Última Atualização:</div>
                        <div class="info-value">{{ $usuario->updated_at ? $usuario->updated_at->format('d/m/Y H:i') : 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <div class="user-rentals">
                <h3><i class="fas fa-book-reader"></i> Histórico de Aluguéis</h3>

                @if(count($usuario->alugueis) > 0)
                    <div class="rentals-list">
                        @foreach($usuario->alugueis as $aluguel)
                            <div class="rental-card">
                                <div class="rental-status-indicator {{ strtolower($aluguel->ds_status) == 'devolvido' ? 'status-returned' : (strtolower($aluguel->ds_status) == 'atrasado' ? 'status-delayed' : 'status-active') }}"></div>
                                <div class="rental-content">
                                    <div class="rental-header">
                                        <div class="rental-title">{{ $aluguel->livro->titulo ?? 'N/A' }}</div>
                                        <span class="rental-badge {{ strtolower($aluguel->ds_status) == 'devolvido' ? 'returned' : (strtolower($aluguel->ds_status) == 'atrasado' ? 'delayed' : 'active') }}">
                                            {{ $aluguel->ds_status }}
                                        </span>
                                    </div>
                                    <div class="rental-info">
                                        <div class="rental-details">
                                            <div class="rental-detail"><i class="fas fa-calendar-alt"></i> Alugado em: {{ \Carbon\Carbon::parse($aluguel->dt_aluguel)->format('d/m/Y') }}</div>
                                            <div class="rental-detail"><i class="fas fa-calendar-check"></i> Devolução: {{ \Carbon\Carbon::parse($aluguel->dt_devolucao)->format('d/m/Y') }}</div>
                                        </div>
                                        <div class="rental-actions">
                                            <a href="{{ route('rentals.show', $aluguel->id_aluguel) }}" class="btn-small">
                                                <i class="fas fa-info-circle"></i> Detalhes
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-rentals">
                        <i class="fas fa-book"></i>
                        <p>Este usuário não possui aluguéis registrados.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="form-actions" style="margin-top: 20px;">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>

            <form action="{{ route('users.destroy', $usuario->id_usuario) }}" method="POST" class="d-inline" style="margin-left: 10px;" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
    @vite(['resources/css/usuario/usuario.css'])
@endpush
