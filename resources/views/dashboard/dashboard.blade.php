@extends('layouts.app')

@section('title', 'Dashboard - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Dashboard')

@section('breadcrumb')
<span>Dashboard</span>
@endsection

@section('content')
<!-- Status Cards -->
<div class="status-cards">
    <div class="card">
        <div class="card-content">
            <div class="card-info">
                <h3>{{ $totalLivros ?? 0 }}</h3>
                <span>Total Livros</span>
            </div>
            <div class="card-icon">
                <i class="fas fa-book"></i>
            </div>
        </div>
        <div class="card-footer">
            <span>
                @if(isset($livrosPercentChange))
                    @if($livrosPercentChange >= 0)
                        <i class="fas fa-arrow-up"></i> {{ $livrosPercentChange }}%
                    @else
                        <i class="fas fa-arrow-down"></i> {{ abs($livrosPercentChange) }}%
                    @endif
                @else
                    <i class="fas fa-arrow-up"></i> 0%
                @endif
            </span>
            <span>Desde o último mês</span>
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            <div class="card-info">
                <h3>{{ $totalAlugueis ?? 0 }}</h3>
                <span>Aluguéis Ativos</span>
            </div>
            <div class="card-icon">
                <i class="fas fa-handshake"></i>
            </div>
        </div>
        <div class="card-footer">
            <span>
                @if(isset($alugueisPeriodChange))
                    @if($alugueisPeriodChange >= 0)
                        <i class="fas fa-arrow-up"></i> {{ $alugueisPeriodChange }}%
                    @else
                        <i class="fas fa-arrow-down"></i> {{ abs($alugueisPeriodChange) }}%
                    @endif
                @else
                    <i class="fas fa-arrow-up"></i> 0%
                @endif
            </span>
            <span>Desde o último mês</span>
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            <div class="card-info">
                <h3>{{ $totalAtrasos ?? 0 }}</h3>
                <span>Atrasos</span>
            </div>
            <div class="card-icon warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="card-footer">
            <span>
                @if(isset($atrasosPeriodChange))
                    @if($atrasosPeriodChange >= 0)
                        <i class="fas fa-arrow-up"></i> {{ $atrasosPeriodChange }}%
                    @else
                        <i class="fas fa-arrow-down"></i> {{ abs($atrasosPeriodChange) }}%
                    @endif
                @else
                    <i class="fas fa-arrow-down"></i> 0%
                @endif
            </span>
            <span>Desde o último mês</span>
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            <div class="card-info">
                <h3>{{ $totalUsuarios ?? 0 }}</h3>
                <span>Usuários</span>
            </div>
            <div class="card-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="card-footer">
            <span>
                @if(isset($usuariosPercentChange))
                    @if($usuariosPercentChange >= 0)
                        <i class="fas fa-arrow-up"></i> {{ $usuariosPercentChange }}%
                    @else
                        <i class="fas fa-arrow-down"></i> {{ abs($usuariosPercentChange) }}%
                    @endif
                @else
                    <i class="fas fa-arrow-up"></i> 0%
                @endif
            </span>
            <span>Desde o último mês</span>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="action-buttons">
    <a href="{{ route('books.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Livro
    </a>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Novo Usuário
    </a>
    <a href="{{ route('rentals.create') }}" class="btn btn-primary">
        <i class="fas fa-handshake"></i> Novo Aluguel
    </a>
</div>

<!-- Charts and Tables Section -->
<div class="dashboard-content">
    <div class="row">
        <!-- Recent Rentals -->
        <div class="col-md-7">
            <div class="panel">
                <div class="panel-header">
                    <h3>Aluguéis Recentes</h3>
                </div>
                <div class="panel-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Livro</th>
                                <th>Usuário</th>
                                <th>Data Aluguel</th>
                                <th>Retorno</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($alugueis->sortByDesc('id_aluguel') ?? [] as $aluguel)
                                <tr class="{{ $aluguel->isAtrasado() ? 'rental-delayed' : '' }}">
                                    <td>{{ $aluguel->livro->titulo ?? 'Livro não encontrado' }}</td>
                                    <td>{{ $aluguel->usuario->nome ?? 'Usuário não encontrado' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($aluguel->dt_aluguel)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($aluguel->dt_devolucao)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="status-badge {{ $aluguel->ds_status == 'Ativo' ? 'active' : ($aluguel->ds_status == 'Atrasado' ? 'overdue' : 'completed') }}">
                                            {{ $aluguel->ds_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum aluguel encontrado</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer">
                    <a href="{{ route('rentals.index') }}" class="view-all">Ver todos os aluguéis <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Popular Books -->
        <div class="col-md-5">
            <div class="panel">
                <div class="panel-header">
                    <h3>Livros Populares</h3>
                </div>
                <div class="panel-body">
                    @forelse($livrosPopulares ?? [] as $livro)
                        <div class="popular-book">
                            <div class="book-cover">
                                @if($livro->capa)
                                    <img src="{{ route('books.capa', $livro->id_livro) }}" alt="Capa do livro" />
                                @else
                                    <i class="fas fa-book"></i>
                                @endif
                            </div>
                            <div class="book-info">
                                <h4>{{ $livro->titulo }}</h4>
                                <p>{{ $livro->autor }}</p>
                                <div class="book-stats">
                                    <span><i class="fas fa-users"></i> {{ $livro->total_alugueis }} aluguéis</span>
                                    <span><i class="fas fa-copy"></i> {{ $livro->quantidade }} exemplares</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-book"></i>
                            <p>Ainda não há dados sobre livros populares.</p>
                        </div>
                    @endforelse
                </div>
                <div class="panel-footer">
                    <a href="{{ route('books.index') }}" class="view-all">Ver catálogo completo <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
