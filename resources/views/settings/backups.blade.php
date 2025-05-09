@extends('layouts.app')

@section('title', 'Gerenciamento de Backups - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Gerenciamento de Backups')

@vite(['resources/css/settings/backup/backup.css'])

@section('breadcrumb')
<a href="{{ route('settings.index') }}">Configurações</a>
<span>Backups</span>
@endsection

@section('content')
<div class="panel panel-modern">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="panel-title d-flex align-items-center" style="gap: 0.75rem;">
                <span class="d-flex align-items-center justify-content-center" style="background:#f0f4fa;border-radius:50%;width:2.5rem;height:2.5rem;margin-right:0.75rem;">
                    <i class="fas fa-server text-primary" style="font-size:1.3rem;"></i>
                </span>
                <span>Backups do Sistema</span>
            </h3>
            <p class="panel-subtitle mt-2">Gerencie os backups do seu sistema para proteção de dados</p>
        </div>
        <div class="panel-actions">
            <form action="{{ route('settings.backup.create') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary btn-with-icon">
                    <i class="fas fa-plus-circle"></i> Criar Novo Backup
                </button>
            </form>
        </div>
    </div>
    
    <div class="panel-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        <div class="backup-manager">
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="info-card">
                        <div class="info-card-header">
                            <i class="fas fa-info-circle text-info"></i>
                            <h5>Sobre os Backups</h5>
                        </div>
                        <div class="info-card-body">
                            <p>Os backups incluem todos os dados do banco de dados e arquivos importantes do sistema. 
                            Recomendamos manter backups regulares para garantir a segurança dos seus dados.</p>
                            <p class="mb-0"><strong>Dica:</strong> Faça o download dos backups e armazene-os em locais diferentes para maior segurança.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="status-card">
                        <div class="status-card-header">
                            <h5>Status de Backup</h5>
                        </div>
                        <div class="status-card-body">
                            <div class="status-item">
                                <span class="status-label">Automático:</span>
                                <span class="status-badge {{ isset($settings['enable_auto_backup']) && $settings['enable_auto_backup'] ? 'badge-success' : 'badge-danger' }}">
                                    {{ isset($settings['enable_auto_backup']) && $settings['enable_auto_backup'] ? 'Ativado' : 'Desativado' }}
                                </span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Frequência:</span>
                                <span class="status-badge badge-info">
                                    @switch($settings['backup_frequency'] ?? 'weekly')
                                        @case('daily')
                                            <i class="fas fa-calendar-day me-1"></i> Diário
                                            @break
                                        @case('weekly')
                                            <i class="fas fa-calendar-week me-1"></i> Semanal
                                            @break
                                        @case('monthly')
                                            <i class="fas fa-calendar-alt me-1"></i> Mensal
                                            @break
                                        @default
                                            {{ $settings['backup_frequency'] ?? 'Semanal' }}
                                    @endswitch
                                </span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Retenção:</span>
                                <span class="status-badge badge-secondary">
                                    <i class="fas fa-history me-1"></i> {{ $settings['backup_retention'] ?? 5 }} backups
                                </span>
                            </div>
                            <div class="mt-3 text-end">
                                <a href="{{ route('settings.index') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-cog"></i> Alterar Configurações
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(count($backups) > 0)
                <div class="backup-list-card mt-4" style="box-shadow:0 2px 12px rgba(0,0,0,0.06);border-radius:0.75rem;overflow:hidden;">
                    <div class="backup-list-header d-flex align-items-center justify-content-between" style="background:#f8f9fa;padding:1.25rem 1.5rem;border-bottom:1px solid #e9ecef;">
                        <div class="d-flex align-items-center" style="gap:0.5rem;">
                            <span class="d-flex align-items-center justify-content-center" style="background:#e7f1ff;border-radius:50%;width:2.2rem;height:2.2rem;margin-right:0.5rem;">
                                <i class="fas fa-history text-primary" style="font-size:1.1rem;"></i>
                            </span>
                            <h5 class="mb-0" style="font-weight:600;font-size:1.05rem;">Backups Disponíveis</h5>
                        </div>
                        <span class="total-backups" style="font-size:0.95rem;color:#6c757d;">{{ count($backups) }} {{ count($backups) == 1 ? 'backup' : 'backups' }} encontrado(s)</span>
                    </div>
                    <div class="table-responsive backup-table-wrapper" style="padding:0;">
                        <table class="table table-hover backup-table mb-0" style="background:#fff;">
                            <thead>
                                <tr style="background:#f8f9fa;">
                                    <th style="font-weight:600;color:#495057;border-top:none;padding:0.85rem 1.25rem;"><i class="fas fa-file-archive me-1"></i> Nome do Arquivo</th>
                                    <th style="font-weight:600;color:#495057;border-top:none;padding:0.85rem 1.25rem;"><i class="fas fa-weight me-1"></i> Tamanho</th>
                                    <th style="font-weight:600;color:#495057;border-top:none;padding:0.85rem 1.25rem;"><i class="fas fa-calendar me-1"></i> Data de Criação</th>
                                    <th class="text-center" style="font-weight:600;color:#495057;border-top:none;padding:0.85rem 1.25rem;"><i class="fas fa-tools me-1"></i> Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($backups as $backup)
                                <tr style="vertical-align:middle;">
                                    <td class="backup-filename">
                                        <div class="filename-container d-flex align-items-center" style="gap:0.5rem;">
                                            <span class="d-flex align-items-center justify-content-center" style="background:#f0f4fa;border-radius:50%;width:2rem;height:2rem;">
                                                <i class="fas fa-file-archive text-primary" style="font-size:1rem;"></i>
                                            </span>
                                            <span style="font-weight:500;">{{ $backup['filename'] }}</span>
                                        </div>
                                    </td>
                                    <td style="font-size:0.98rem;">{{ $backup['size'] }}</td>
                                    <td style="font-size:0.98rem;">{{ $backup['created_at'] }}</td>
                                    <td class="actions text-center">
                                        <div class="action-buttons">
                                            <a href="{{ route('settings.backup.download', $backup['filename']) }}" class="btn btn-sm btn-primary btn-download" title="Download">
                                                <i class="fas fa-download me-1"></i> Download
                                            </a>
                                            <form action="{{ route('settings.backup.delete', $backup['filename']) }}" method="POST" class="d-inline delete-form ms-2">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                    <i class="fas fa-trash me-1"></i> Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-container">
                        <div class="empty-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <h4>Nenhum backup encontrado</h4>
                        <p>Não há backups disponíveis no momento. Crie seu primeiro backup para proteger seus dados.</p>
                        <form action="{{ route('settings.backup.create') }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-with-icon">
                                <i class="fas fa-plus-circle"></i> Criar Primeiro Backup
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('.delete-form');
        
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Esta ação não pode ser desfeita! O backup será excluído permanentemente.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
        
        // Fechar alertas automaticamente após 5 segundos
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.click();
                }
            }, 5000);
        });
    });
</script>
@endpush
@endsection