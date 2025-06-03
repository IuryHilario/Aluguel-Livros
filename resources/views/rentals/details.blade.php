@extends('layouts.app')

@section('title', 'Detalhes do Aluguel - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Detalhes do Aluguel')

@vite(['resources/css/rentals/rentals.css'])

@section('breadcrumb')
<a href="{{ route('rentals.index') }}">Aluguéis</a> / <span>Detalhes</span>
@endsection

@section('content')
<div class="details-container">
    <!-- Cabeçalho com ações -->
    <div class="details-header">
        <div class="header-info">
            <h2>Aluguel N° {{ $aluguel->id_aluguel }}</h2>
            <div class="status-chip {{ $aluguel->ds_status == 'Ativo' ? 'active' : ($aluguel->ds_status == 'Devolvido' ? 'completed' : 'delayed') }}">
                {{ $aluguel->ds_status }}
                @if($aluguel->isAtrasado())
                    <span class="delay-info">({{ $aluguel->diasAtraso() }} dias)</span>
                @endif
            </div>
        </div>
        <div class="header-actions">
            @if($aluguel->ds_status != 'Devolvido')
                @if($aluguel->podeRenovar())
                <x-form.actions
                    :renovar="route('rentals.renew', $aluguel->id_aluguel)"
                />
                @endif
                <x-form.actions
                    :devolver="route('rentals.return', $aluguel->id_aluguel)"
                />
            @endif
            <a href="{{ route('rentals.index') }}" class="action-button back-button">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Cartões de informação -->
    <div class="detail-cards">
        <!-- Cartão do Livro -->
        <div class="detail-card book-card">
            <div class="card-header">
                <h3><i class="fas fa-book"></i> Livro</h3>
            </div>
            <div class="card-body">
                <div class="book-cover">
                    @if($aluguel->livro->capa)
                        <img src="{{ route('books.capa', $aluguel->id_livro) }}" alt="{{ $aluguel->livro->titulo }}">
                    @else
                        <div class="no-cover">
                            <i class="fas fa-book"></i>
                        </div>
                    @endif
                </div>
                <div class="book-details">
                    <h4>{{ $aluguel->livro->titulo }}</h4>
                    <p class="author">{{ $aluguel->livro->autor }}</p>
                    <div class="book-meta">
                        @if($aluguel->livro->isbn)
                            <div class="meta-item">
                                <i class="fas fa-barcode"></i> ISBN: {{ $aluguel->livro->isbn }}
                            </div>
                        @endif
                        @if($aluguel->livro->categoria)
                            <div class="meta-item">
                                <i class="fas fa-tag"></i> {{ $aluguel->livro->categoria }}
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('books.show', $aluguel->id_livro) }}" class="view-more">
                        Ver detalhes do livro <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Cartão do Usuário -->
        <div class="detail-card user-card">
            <div class="card-header">
                <h3><i class="fas fa-user"></i> Usuário</h3>
            </div>
            <div class="card-body">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <h4>{{ $aluguel->usuario->nome }}</h4>
                    <div class="user-meta">
                        <div class="meta-item">
                            <i class="fas fa-envelope"></i> {{ $aluguel->usuario->email }}
                        </div>
                        @if($aluguel->usuario->telefone)
                            <div class="meta-item">
                                <i class="fas fa-phone"></i> {{ $aluguel->usuario->telefone }}
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('users.show', $aluguel->id_usuario) }}" class="view-more">
                        Ver perfil do usuário <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Linha do Tempo e Renovações -->
    <div class="detail-cards">
        <!-- Linha do Tempo -->
        <div class="detail-card timeline-card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-alt"></i> Linha do Tempo</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item completed">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-date">{{ date('d/m/Y', strtotime($aluguel->dt_aluguel)) }}</div>
                            <h4 class="timeline-title">Retirada</h4>
                            <p>Livro retirado pelo usuário</p>
                        </div>
                    </div>

                    <div class="timeline-item {{ $aluguel->isAtrasado() ? 'delayed' : ($aluguel->ds_status == 'Devolvido' ? 'completed' : 'current') }}">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-date">{{ date('d/m/Y', strtotime($aluguel->dt_devolucao)) }}</div>
                            <h4 class="timeline-title">Devolução Prevista</h4>
                            <p>
                                @if($aluguel->isAtrasado())
                                    Data de devolução excedida
                                @elseif($aluguel->ds_status == 'Devolvido')
                                    Data prevista inicialmente
                                @else
                                    Data limite para devolução
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($aluguel->dt_devolucao_efetiva)
                    <div class="timeline-item completed">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-date">{{ date('d/m/Y', strtotime($aluguel->dt_devolucao_efetiva)) }}</div>
                            <h4 class="timeline-title">Devolução Efetiva</h4>
                            <p>Livro devolvido à biblioteca</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Renovações -->
        <div class="detail-card renewals-card">
            <div class="card-header">
                <h3><i class="fas fa-sync-alt"></i> Renovações</h3>
            </div>
            <div class="card-body">
                @php
                    // Obtém o valor da configuração de renovações máximas
                    $maxRenovacoesPermitidas = intval($settings['max_renewals'] ?? 2);
                    $porcentagem = ($aluguel->nu_renovacoes / $maxRenovacoesPermitidas) * 100;
                @endphp

                <div class="renewal-counter">
                    <div class="counter-numbers">
                        <span class="current">{{ $aluguel->nu_renovacoes }}</span>
                        <span class="divider">/</span>
                        <span class="max">{{ $maxRenovacoesPermitidas }}</span>
                    </div>
                    <div class="counter-label">Renovações realizadas</div>
                </div>

                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ min($porcentagem, 100) }}%"></div>
                    </div>
                    <div class="progress-labels">
                        <span>0</span>
                        <span>{{ $maxRenovacoesPermitidas }}</span>
                    </div>
                </div>

                @if($aluguel->ds_status != 'Devolvido')
                <div class="renewal-status">
                    @if($aluguel->podeRenovar())
                    <div class="status-indicator positive">
                        <i class="fas fa-check-circle"></i>
                        <div class="status-text">
                            <h4>Disponível para renovação</h4>
                            <p>Este empréstimo pode ser renovado por mais dias.</p>
                        </div>
                    </div>
                    @else
                    <div class="status-indicator negative">
                        <i class="fas fa-times-circle"></i>
                        <div class="status-text">
                            <h4>Não disponível para renovação</h4>
                            <p>
                                @if($aluguel->nu_renovacoes >= $maxRenovacoesPermitidas)
                                    Limite máximo de renovações atingido.
                                @elseif($aluguel->isAtrasado())
                                    O empréstimo está atrasado.
                                @else
                                    Não é possível renovar no momento.
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Histórico de renovações -->
                @if($aluguel->nu_renovacoes > 0)
                <div class="renewal-history">
                    <h4>Histórico de renovações</h4>
                    <div class="history-entry">
                        <div class="entry-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="entry-content">
                            <h5>Última renovação</h5>
                            <p>{{ $aluguel->ultima_renovacao ? date('d/m/Y', strtotime($aluguel->ultima_renovacao)) : 'Data não registrada' }}</p>
                        </div>
                    </div>
                </div>
                @else
                <div class="no-renewals">
                    <i class="fas fa-info-circle"></i>
                    <p>Este aluguel ainda não foi renovado</p>
                </div>
                @endif
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
    document.querySelectorAll('.return-button').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const href = btn.getAttribute('href');
            Swal.fire({
                title: 'Confirmar Devolução',
                text: 'Tem certeza que deseja registrar a devolução deste livro?',
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

    document.querySelectorAll('.renew-button, .action-btn.renew').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const href = btn.getAttribute('href');
            Swal.fire({
                title: 'Confirmar Renovação',
                text: 'Tem certeza que deseja renovar este empréstimo?',
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
