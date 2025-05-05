@extends('layouts.app')

@section('title', 'Relatórios - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Relatórios')

@vite(['resources/css/reports/reports.css'])

@section('breadcrumb')
<span>Relatórios</span>
@endsection

@section('content')
<div class="dashboard-summary">
    <div class="summary-cards">
        <div class="card blue">
            <div class="card-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="card-data">
                <h3>{{ $reportData['totalBooks'] }}</h3>
                <span>Livros</span>
            </div>
        </div>
        
        <div class="card purple">
            <div class="card-icon">
                <i class="fas fa-copy"></i>
            </div>
            <div class="card-data">
                <h3>{{ $reportData['totalCopies'] }}</h3>
                <span>Exemplares</span>
            </div>
        </div>
        
        <div class="card green">
            <div class="card-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-data">
                <h3>{{ $reportData['totalUsers'] }}</h3>
                <span>Usuários</span>
            </div>
        </div>
        
        <div class="card orange">
            <div class="card-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="card-data">
                <h3>{{ $reportData['activeRentals'] }}</h3>
                <span>Aluguéis Ativos</span>
            </div>
        </div>
        
        <div class="card red">
            <div class="card-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="card-data">
                <h3>{{ $reportData['overdueRentals'] }}</h3>
                <span>Atrasos</span>
            </div>
        </div>
    </div>
</div>

<!-- Estatísticas Mensais -->
<div class="panel">
    <div class="panel-header">
        <h3>Estatísticas Mensais (Últimos 6 Meses)</h3>
        <div class="panel-actions">
            <a href="{{ route('reports.pdf') }}" class="btn-icon" title="Exportar para PDF">
                <i class="fas fa-file-pdf"></i>
            </a>
        </div>
    </div>
    <div class="panel-body">
        <div class="monthly-stats">
            <table class="stats-table">
                <thead>
                    <tr>
                        <th>Mês</th>
                        <th>Novos Aluguéis</th>
                        <th>Devoluções</th>
                        <th>Atrasos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyStats as $stats)
                        <tr>
                            <td>{{ $stats['month'] }}</td>
                            <td>{{ $stats['newRentals'] }}</td>
                            <td>{{ $stats['returnedRentals'] }}</td>
                            <td>{{ $stats['overdueCount'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="reports-row">
    <!-- Livros Mais Populares -->
    <div class="panel half-panel">
        <div class="panel-header">
            <h3>Livros Mais Populares</h3>
        </div>
        <div class="panel-body">
            @if(count($topBooks) > 0)
                <div class="popular-books">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>Aluguéis</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topBooks as $book)
                                <tr>
                                    <td>{{ $book->titulo }}</td>
                                    <td>{{ $book->autor }}</td>
                                    <td>{{ $book->aluguel_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-book"></i>
                    <p>Sem dados de livros para exibir.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Usuários Mais Ativos -->
    <div class="panel half-panel">
        <div class="panel-header">
            <h3>Usuários Mais Ativos</h3>
        </div>
        <div class="panel-body">
            @if(count($activeUsers) > 0)
                <div class="active-users">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Aluguéis</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeUsers as $user)
                                <tr>
                                    <td>{{ $user->nome }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->aluguel_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>Sem dados de usuários para exibir.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Aluguéis em Atraso -->
<div class="panel">
    <div class="panel-header">
        <h3>Aluguéis em Atraso</h3>
        <div class="panel-actions">
            <button class="btn-icon" id="toggleOverdueFilter" title="Filtrar">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </div>
    
    <div class="overdue-filter-container" style="display: none;">
        <form id="overdueFilterForm" class="filter-form">
            <div class="filter-row">
                <div class="filter-column">
                    <label for="start_date">Data Início</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="filter-column">
                    <label for="end_date">Data Fim</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="filter-column">
                    <label for="min_days">Mínimo de Dias em Atraso</label>
                    <input type="number" id="min_days" name="min_days" class="form-control" value="{{ request('min_days') }}" min="1">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <button type="button" id="clearFilters" class="btn btn-secondary">Limpar</button>
                </div>
            </div>
            <div id="filter-loading" class="mt-2" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> Carregando...
            </div>
        </form>
    </div>
    
    <div class="panel-body" id="overdueRentalsContent">
        @if(count($overdueRentals) > 0)
            <div class="overdue-rentals">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Livro</th>
                            <th>Data Devolução</th>
                            <th>Dias em Atraso</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overdueRentals as $rental)
                            <tr class="overdue-row">
                                <td>{{ $rental->id_aluguel }}</td>
                                <td>{{ $rental->usuario->nome }}</td>
                                <td>{{ $rental->livro->titulo }}</td>
                                <td>{{ \Carbon\Carbon::parse($rental->dt_devolucao)->format('d/m/Y') }}</td>
                                <td>{{ $rental->diasAtraso() }} dias</td>
                                <td class="actions">
                                    <a href="{{ route('rentals.show', $rental->id_aluguel) }}" class="action-btn details" title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>Não há aluguéis em atraso no momento.</p>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Notification buttons functionality
        const notifyButtons = document.querySelectorAll('.notify');
        notifyButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-id');
                alert(`Funcionalidade de notificação para o usuário ID: ${userId} será implementada em breve.`);
            });
        });
        
        // Notify all button
        const notifyAllBtn = document.getElementById('notifyAllBtn');
        if (notifyAllBtn) {
            notifyAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                alert('Funcionalidade de notificação para todos os usuários em atraso será implementada em breve.');
            });
        }
        
        // Toggle overdue filter
        const toggleOverdueFilter = document.getElementById('toggleOverdueFilter');
        const overdueFilterContainer = document.querySelector('.overdue-filter-container');
        
        // Toggle para mostrar/ocultar o filtro
        if (toggleOverdueFilter && overdueFilterContainer) {
            toggleOverdueFilter.addEventListener('click', function() {
                if (overdueFilterContainer.style.display === 'none' || !overdueFilterContainer.style.display) {
                    overdueFilterContainer.style.display = 'block';
                } else {
                    overdueFilterContainer.style.display = 'none';
                }
            });
        }
        
        // AJAX form submission
        const overdueFilterForm = document.getElementById('overdueFilterForm');
        const overdueRentalsContent = document.getElementById('overdueRentalsContent');
        const filterLoading = document.getElementById('filter-loading');
        
        if (overdueFilterForm && overdueRentalsContent) {
            overdueFilterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validação de datas
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                
                if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                    alert('A data inicial não pode ser maior que a data final');
                    return false;
                }
                
                // Mostrar indicador de carregamento
                if (filterLoading) {
                    filterLoading.style.display = 'block';
                }
                
                // Montar os parâmetros do filtro
                const formData = new FormData(overdueFilterForm);
                const params = new URLSearchParams();
                
                for (const [key, value] of formData.entries()) {
                    if (value) {
                        params.append(key, value);
                    }
                }
                
                // Fazer a requisição AJAX
                fetch(`{{ route('reports.overdue-filter') }}?${params.toString()}`)
                    .then(response => response.text())
                    .then(html => {
                        // Atualizar o conteúdo com os resultados filtrados
                        overdueRentalsContent.innerHTML = html;
                        
                        // Registrar novamente os listeners para os botões de notificação
                        const newNotifyButtons = overdueRentalsContent.querySelectorAll('.notify');
                        newNotifyButtons.forEach(button => {
                            button.addEventListener('click', function(e) {
                                e.preventDefault();
                                const userId = this.getAttribute('data-id');
                                alert(`Funcionalidade de notificação para o usuário ID: ${userId} será implementada em breve.`);
                            });
                        });
                        
                        // Ocultar indicador de carregamento
                        if (filterLoading) {
                            filterLoading.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao filtrar aluguéis em atraso:', error);
                        if (filterLoading) {
                            filterLoading.style.display = 'none';
                        }
                        alert('Ocorreu um erro ao filtrar os aluguéis. Por favor, tente novamente.');
                    });
            });
            
            // Limpar filtros
            const clearFilters = document.getElementById('clearFilters');
            if (clearFilters) {
                clearFilters.addEventListener('click', function() {
                    // Limpar campos de filtro
                    document.getElementById('start_date').value = '';
                    document.getElementById('end_date').value = '';
                    document.getElementById('min_days').value = '';
                    
                    // Submeter o form para mostrar todos os aluguéis em atraso
                    overdueFilterForm.dispatchEvent(new Event('submit'));
                });
            }
        }
    });
</script>
@endpush