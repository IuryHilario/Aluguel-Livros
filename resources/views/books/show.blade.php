@extends('layouts.app')

@section('title', $livro->titulo . ' - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Detalhes do Livro')

@vite(['resources/css/books/books.css'])

@section('breadcrumb')
<a href="{{ route('books.index') }}">Livros</a> / <span>Detalhes</span>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3>{{ $livro->titulo }}</h3>
        <div class="panel-actions">
            <x-form.actions
                edit="{{ route('books.edit', $livro->id_livro) }}"
                delete="{{ route('books.destroy', $livro->id_livro) }}"
            />
        </div>
    </div>
    <div class="panel-body">
        <div class="book-details">
            <div class="book-cover-container">
                @if($livro->capa)
                    <img src="{{ route('books.capa', $livro->id_livro) }}" alt="Capa do livro {{ $livro->titulo }}" class="book-cover-large">
                @else
                    <div class="no-cover-large">
                        <i class="fas fa-book fa-4x"></i>
                    </div>
                @endif

                <div class="book-status mt-3">
                    <h4>Status</h4>
                    <div class="status-info">
                        <p>Total de exemplares: <strong>{{ $livro->quantidade }}</strong></p>
                        <p>Disponíveis: <strong>{{ $livro->quantidade_disponivel }}</strong></p>
                        <p class="mt-2">
                            @if($livro->quantidade_disponivel > 0)
                                <span class="status-badge status-available">
                                    <i class="fas fa-check-circle"></i> Disponível para empréstimo
                                </span>
                            @else
                                <span class="status-badge status-unavailable">
                                    <i class="fas fa-times-circle"></i> Indisponível
                                </span>
                            @endif
                        </p>
                    </div>

                    @if($livro->quantidade_disponivel > 0)
                    <div class="mt-3">
                        <a href="{{ route('rentals.create', ['book_id' => $livro->id_livro]) }}" class="btn btn-primary btn-block">
                            <i class="fas fa-book-reader"></i> Realizar Empréstimo
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <div class="book-info-container">
                <div class="book-meta">
                    <p class="book-author"><strong><i class="fas fa-user-edit"></i> Autor:</strong> {{ $livro->autor }}</p>
                    @if($livro->editor)
                        <p class="book-editor"><strong><i class="fas fa-building"></i> Editora:</strong> {{ $livro->editor }}</p>
                    @endif
                    <p class="book-year"><strong><i class="fas fa-calendar-alt"></i> Ano de Publicação:</strong> {{ $livro->ano_publicacao }}</p>
                </div>

                <div class="book-history">
                    <h4><i class="fas fa-history"></i> Histórico de Empréstimos</h4>

                    @if(!isset($livro->alugueis) || $livro->alugueis->isEmpty())
                        <div class="empty-history">
                            <i class="fas fa-book"></i>
                            <p>Este livro ainda não foi emprestado.</p>
                        </div>
                    @else
                        <div class="history-list">
                            @foreach($livro->alugueis as $aluguel)
                            <div class="history-item">
                                <div class="history-status">
                                    <span class="status-circle status-{{ strtolower($aluguel->ds_status) }}"></span>
                                </div>
                                <div class="history-details">
                                    <div class="history-header">
                                        <h5>{{ $aluguel->usuario->nome ?? 'Usuário não encontrado' }}</h5>
                                        <span class="history-date">{{ date('d/m/Y', strtotime($aluguel->dt_aluguel)) }}</span>
                                    </div>
                                    <div class="history-info">
                                        <div class="history-badge status-{{ strtolower($aluguel->ds_status) }}">
                                            {{ $aluguel->ds_status }}
                                        </div>
                                        <div class="history-return">
                                            <i class="fas fa-calendar-check"></i> Devolução prevista: {{ date('d/m/Y', strtotime($aluguel->dt_devolucao)) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-delete-book').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = btn.closest('form');
            Swal.fire({
                title: 'Excluir Livro',
                text: 'Tem certeza que deseja excluir este livro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
