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
                    <input type="text" id="user" name="user" class="form-control" placeholder="Nome do usuário" value="{{ request('user') }}" autocomplete="off">
                </div>
                <div class="filter-column">
                    <label for="book">Livro</label>
                    <input type="text" id="book" name="book" class="form-control" placeholder="Título do livro" value="{{ request('book') }}" autocomplete="off">
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
                                        <x-form.actions
                                            :show="route('rentals.show', $aluguel->id_aluguel)"
                                            :devolver="route('rentals.return', $aluguel->id_aluguel)"
                                            :email="$aluguel->ds_status == 'Atrasado' ? route('rentals.notification', $aluguel->id_aluguel) : null"
                                        />
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

<!-- Modal de confirmação para reenvio de e-mail de atraso -->
<div class="modal" id="emailModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #fee2e2;">
                <span style="font-size: 1.7rem; color: #ef4444; margin-right: 10px;"><i class="fas fa-envelope"></i></span>
                <h5 class="modal-title" style="color: #ef4444;">Reenviar E-mail de Atraso</h5>
                <button type="button" class="close" id="closeEmailModal">&times;</button>
            </div>
            <div class="modal-body" style="text-align: center;">
                <p style="font-size: 1.1rem; color: #b91c1c; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle" style="color: #ef4444; margin-right: 8px;"></i>
                    Deseja reenviar o e-mail de atraso para <span id="modalUserName"></span>?
                </p>
                <div style="display: flex; justify-content: center; gap: 20px;">
                    <button class="btn btn-primary" id="confirmEmailSend"><i class="fas fa-paper-plane"></i> Sim</button>
                    <button class="btn btn-secondary" id="cancelEmailSend">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

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

        // Modal de confirmação de e-mail de atraso
        let selectedRentalId = null;
        const emailModal = document.getElementById('emailModal');
        const closeEmailModal = document.getElementById('closeEmailModal');
        const cancelEmailSend = document.getElementById('cancelEmailSend');
        const confirmEmailSend = document.getElementById('confirmEmailSend');
        document.querySelectorAll('.action-btn.email').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                selectedRentalId = btn.getAttribute('data-rental-id');
                // Buscar o nome do usuário na mesma linha da tabela
                var userName = btn.closest('tr').querySelector('td:nth-child(2) .user-link').textContent.trim();
                document.getElementById('modalUserName').textContent = userName;
                emailModal.style.display = 'block';
            });
        });
        function closeModal() {
            emailModal.style.display = 'none';
            selectedRentalId = null;
        }
        closeEmailModal.addEventListener('click', closeModal);
        cancelEmailSend.addEventListener('click', closeModal);
        window.addEventListener('click', function(event) {
            if (event.target === emailModal) closeModal();
        });
        confirmEmailSend.addEventListener('click', function() {
            if (selectedRentalId) {
                window.location.href = "{{ url('rentals/notification') }}/" + selectedRentalId;
            }
        });

        document.querySelectorAll('.btn-return').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const href = btn.getAttribute('href');
                Swal.fire({
                    title: 'Confirmar Devolução',
                    text: 'Tem certeza que deseja marcar este livro como devolvido?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });
        });
    });
</script>
@endpush
