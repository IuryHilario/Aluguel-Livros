@extends('layouts.app')

@section('title', 'Histórico de Aluguéis - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Histórico de Aluguéis')

@vite(['resources/css/rentals/rentals.css'])

@section('breadcrumb')
<a href="{{ route('rentals.index') }}">Aluguéis</a> / <span>Histórico</span>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3>Histórico Completo</h3>
        <div class="panel-actions">
            <button class="btn-icon" id="toggleFilter"><i class="fas fa-filter"></i></button>
        </div>
    </div>

    <div class="filter-container" style="display: none;">
        <form action="{{ route('rentals.history') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-column">
                    <label for="user">Usuário</label>
                    <input type="text" id="user" name="user" class="form-control" placeholder="Nome do usuário" value="{{ request('user') }}" autocomplete="off">
                </div>
                <div class="filter-column">
                    <label for="book">Livro</label>
                    <input type="text" id="book" name="book" class="form-control" placeholder="Título do livro" value="{{ request('book') }}" autocomplete="off">
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
                    <a href="{{ route('rentals.history') }}" class="btn btn-secondary">Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="panel-body">
        @if(count($alugueis) > 0)
            <div class="table-responsive">
                <table class="rentals-table">
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Livro</th>
                            <th>Data Aluguel</th>
                            <th>Data Devolução</th>
                            <th>Duração</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alugueis as $aluguel)
                            <tr>
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
                                <td>
                                    {{ \Carbon\Carbon::parse($aluguel->dt_aluguel)->diffInDays(\Carbon\Carbon::parse($aluguel->dt_devolucao)) }} dias
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                {{ $alugueis->links('components.pagination') }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-history"></i>
                </div>
                <h4>Nenhum histórico encontrado</h4>
                <p>Não existem registros de devoluções até o momento.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleFilter = document.getElementById('toggleFilter');
        const filterContainer = document.querySelector('.filter-container');

        // Verificar se há filtros aplicados
        const urlParams = new URLSearchParams(window.location.search);
        const hasFilters = urlParams.has('user') || urlParams.has('book') ||
                          urlParams.has('start_date') || urlParams.has('end_date');

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
@endsection
