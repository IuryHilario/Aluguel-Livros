@extends('layouts.app')

@section('title', 'Aluguéis - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Aluguéis Ativos')

@vite(['resources/css/rentals/rentals.css'])

@section('breadcrumb')
<a href="{{ route('rentals.index') }}">Aluguéis</a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3>Aluguéis Ativos</h3>
        <div class="panel-actions">
            <button class="btn-icon" id="toggleFilter"><i class="fas fa-filter"></i></button>
            <a href="{{ route('rentals.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Aluguel
            </a>
        </div>
    </div>
    
    <div class="filter-container" style="display: none;">
        <form action="{{ route('rentals.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-column">
                    <label for="user">Usuário</label>
                    <input type="text" id="user" name="user" class="form-control" placeholder="Nome do usuário" value="{{ request('user') }}">
                </div>
                <div class="filter-column">
                    <label for="book">Livro</label>
                    <input type="text" id="book" name="book" class="form-control" placeholder="Título do livro" value="{{ request('book') }}">
                </div>
                <div class="filter-column">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="Ativo" {{ request('status') == 'Ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="Atrasado" {{ request('status') == 'Atrasado' ? 'selected' : '' }}>Atrasado</option>
                    </select>
                </div>
                <div class="filter-column">
                    <label for="start_date">Data Inicial</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="filter-column">
                    <label for="end_date">Data Final</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('rentals.index') }}" class="btn btn-secondary">Limpar</a>
                </div>
            </div>
        </form>
    </div>
    
    <div class="panel-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        
        @if(count($alugueis) > 0)
            <div class="table-responsive">
                <table class="rentals-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Livro</th>
                            <th>Data Saída</th>
                            <th>Data Devolução</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alugueis as $aluguel)
                            <tr class="{{ $aluguel->isAtrasado() ? 'rental-delayed' : '' }}">
                                <td>{{ $aluguel->id_aluguel }}</td>
                                <td>
                                    <a href="{{ route('users.show', $aluguel->id_usuario) }}" class="user-link">
                                        {{ $aluguel->usuario->nome }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('books.show', $aluguel->id_livro) }}" class="book-link">
                                        {{ $aluguel->livro->titulo }}
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($aluguel->dt_aluguel)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($aluguel->dt_devolucao)->format('d/m/Y') }}</td>
                                <td class="status-cell">
                                    <span class="status-badge {{ $aluguel->ds_status == 'Ativo' ? 'active' : 'delayed' }}">
                                        {{ $aluguel->ds_status }}
                                    </span>
                                    @if($aluguel->isAtrasado())
                                        <div class="delay-days">({{ $aluguel->diasAtraso() }} dias)</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('rentals.show', $aluguel->id_aluguel) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Detalhes
                                        </a>
                                        <a href="{{ route('rentals.return', $aluguel->id_aluguel) }}" 
                                           class="btn btn-sm btn-primary return-btn"
                                           onclick="return confirm('Tem certeza que deseja marcar este livro como devolvido?')">
                                            <i class="fas fa-undo"></i> Devolver
                                        </a>
                                        @if($aluguel->ds_status == 'Atrasado')
                                        <a href="{{ route('rentals.notification', $aluguel->id_aluguel) }}" 
<<<<<<< HEAD
                                           class="btn btn-sm btn-email"
                                           onclick="return confirm('Enviar email de atraso novamente?')">
                                            <i class="fas fa-envelope"></i>  
=======
                                           class="btn btn-sm btn-primary return-btn"
                                           onclick="return confirm('Enviar email de atraso novamente?')">
                                            <i class="fas fa-envelope"></i> Email   
>>>>>>> 40d1f0c1cfe9921ebe650e8bc6e1a57645675b33
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if(method_exists($alugueis, 'links'))
                <div class="pagination-container">
                    {{ $alugueis->appends(request()->query())->links('components.pagination') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-books"></i>
                </div>
                <h4>Nenhum aluguel ativo</h4>
                <p>Não existem aluguéis ativos no momento.</p>
                <a href="{{ route('rentals.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Registrar Aluguel
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleFilter = document.getElementById('toggleFilter');
        const filterContainer = document.querySelector('.filter-container');
        
        // Verificar se há filtros aplicados
        const urlParams = new URLSearchParams(window.location.search);
        const hasFilters = urlParams.has('user') || urlParams.has('book') || 
                          urlParams.has('status') || urlParams.has('start_date') || 
                          urlParams.has('end_date');
        
        if (hasFilters) {
            filterContainer.style.display = 'block';
        }
        
        toggleFilter.addEventListener('click', function() {
            filterContainer.style.display = filterContainer.style.display === 'none' ? 'block' : 'none';
        });
    });
</script>
@endpush