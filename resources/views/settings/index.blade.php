@extends('layouts.app')

@section('title', 'Configurações - '  . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Configurações')

@vite(['resources/css/settings/settings.css'])

@section('breadcrumb')
<span>Configurações</span>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3>Configurações do Sistema</h3>
    </div>
    <div class="panel-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="settings-tabs">
            <button class="tab-button active" data-tab="general">Geral</button>
            <button class="tab-button" data-tab="loans">Empréstimos</button>
            <button class="tab-button" data-tab="notifications">Notificações</button>
            <button class="tab-button" data-tab="backup">Backup</button>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" class="settings-form">
            @csrf

            <!-- Configurações Gerais -->
            <div class="tab-content active" id="general-content">
                <div class="settings-section">
                    <h4>Informações da Biblioteca</h4>

                    <x-form.input
                        label="Nome da Biblioteca"
                        placeholder="Digite o nome da biblioteca"
                        type="text"
                        name="settings[system_name]"
                        id="system_name"
                        value="{{ $settings['system_name'] ?? 'Aluga Livros' }}"
                        required
                    />
                </div>

                <div class="settings-section">
                    <h4>Interface de Usuário</h4>

                    <div class="form-group checkbox-group">
                        <input type="hidden" name="settings[show_book_covers]" value="0">
                        <input type="checkbox" id="show_book_covers" name="settings[show_book_covers]" value="1"
                               {{ isset($settings['show_book_covers']) && $settings['show_book_covers'] ? 'checked' : '' }}>
                        <label for="show_book_covers">Mostrar capas dos livros nas listagens</label>
                    </div>

                    <div class="form-group">
                        <label for="items_per_page">Itens por página</label>
                        <select id="items_per_page" name="settings[items_per_page]" class="form-control">
                            <option value="10" {{ ($settings['items_per_page'] ?? 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ ($settings['items_per_page'] ?? 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ ($settings['items_per_page'] ?? 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($settings['items_per_page'] ?? 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Configurações de Empréstimos -->
            <div class="tab-content" id="loans-content">
                <div class="settings-section">
                    <h4>Regras de Empréstimo</h4>

                    <x-form.input
                        label="Periodo de Empréstimo (dias)"
                        placeholder="Digite o período de empréstimo"
                        type="number"
                        name="settings[loan_period]"
                        id="loan_period"
                        value="{{ $settings['loan_period'] ?? 14 }}"
                        min="1"
                        max="60"
                        required
                    />

                    <x-form.input
                        label="Máximo Livros por Usuário"
                        placeholder="Digite o máximo de livros por usuário"
                        type="number"
                        name="settings[max_loans_per_user]"
                        id="max_loans_per_user"
                        value="{{ $settings['max_loans_per_user'] ?? 3 }}"
                        min="1"
                        max="10"
                        required
                    />
                </div>

                <div class="settings-section">
                    <h4>Renovações</h4>

                    <x-form.input
                        label="Número Máximo Renovações Permitidas"
                        placeholder="Digite o número máximo de renovações permitidas"
                        type="number"
                        name="settings[max_renewals]"
                        id="max_renewals"
                        value="{{ $settings['max_renewals'] ?? 2 }}"
                        min="0"
                        max="5"
                        required
                    />

                    <div class="form-group checkbox-group">
                        <input type="hidden" name="settings[allow_renewal_with_pending]" value="0">
                        <input type="checkbox" id="allow_renewal_with_pending" name="settings[allow_renewal_with_pending]" value="1"
                               {{ isset($settings['allow_renewal_with_pending']) && $settings['allow_renewal_with_pending'] ? 'checked' : '' }}>
                        <label for="allow_renewal_with_pending">Permitir renovações com outras pendências</label>
                    </div>
                </div>
            </div>

            <!-- Configurações de Notificações -->
            <div class="tab-content" id="notifications-content">
                <div class="settings-section">
                    <h4>E-mail</h4>

                    <div class="form-group checkbox-group">
                        <input type="hidden" name="settings[enable_email_notifications]" value="0">
                        <input type="checkbox" id="enable_email_notifications" name="settings[enable_email_notifications]" value="1"
                               {{ isset($settings['enable_email_notifications']) && $settings['enable_email_notifications'] ? 'checked' : '' }}>
                        <label for="enable_email_notifications">Ativar notificações por e-mail</label>
                    </div>

                    <div class="email-notification-settings" id="email-settings" style="{{ isset($settings['enable_email_notifications']) && $settings['enable_email_notifications'] ? '' : 'display: none;' }}">
                        <div class="form-group">
                            <label for="email_from_name">Nome de remetente</label>
                            <input type="text" id="email_from_name" name="settings[email_from_name]" class="form-control" autocomplete="off"
                                   value="{{ $settings['email_from_name'] ?? 'Aluga Livros' }}">
                        </div>

                        <div class="form-group">
                            <label for="email_from_address">E-mail de remetente</label>
                            <input type="email" id="email_from_address" name="settings[email_from_address]" class="form-control"   autocomplete="off"
                                   value="{{ $settings['email_from_address'] ?? 'noreply@alugalivros.com' }}">
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h4>Alertas</h4>

                    <x-form.input
                        label="Dias antes do vencimento para enviar lembrete"
                        placeholder="Digite o número de dias"
                        type="number"
                        name="settings[days_before_due_reminder]"
                        id="days_before_due_reminder"
                        value="{{ $settings['days_before_due_reminder'] ?? 2 }}"
                        min="1"
                        max="7"
                        required
                    />

                    <div class="form-group checkbox-group">
                        <input type="hidden" name="settings[send_overdue_notices]" value="0">
                        <input type="checkbox" id="send_overdue_notices" name="settings[send_overdue_notices]" value="1"
                               {{ isset($settings['send_overdue_notices']) && $settings['send_overdue_notices'] ? 'checked' : '' }}>
                        <label for="send_overdue_notices">Enviar notificações de atraso</label>
                    </div>

                    <x-form.input
                        label="Frequência de notificações de atraso (dias)"
                        placeholder="Digite a frequência de notificações"
                        type="number"
                        name="settings[overdue_notice_frequency]"
                        id="overdue_notice_frequency"
                        value="{{ $settings['overdue_notice_frequency'] ?? 3 }}"
                        min="1"
                        max="7"
                        required
                    />

                </div>
            </div>

            <div class="tab-content" id="backup-content">
                <div class="settings-section">
                    <h4>Backup do Sistema</h4>

                    <div class="settings-section" style="margin-bottom: 20px;">
                        <div class="form-group checkbox-group">
                            <input type="hidden" name="settings[enable_auto_backup]" value="0">
                            <input type="checkbox" id="enable_auto_backup" name="settings[enable_auto_backup]" value="1"
                                   {{ isset(
                                       $settings['enable_auto_backup']) && $settings['enable_auto_backup'] ? 'checked' : '' }}>
                            <label for="enable_auto_backup">Ativar backup automático</label>
                        </div>

                        <div id="backup-settings" style="{{ isset($settings['enable_auto_backup']) && $settings['enable_auto_backup'] ? '' : 'display: none;' }}">
                            <div class="form-group">
                                <label for="backup_frequency">Frequência de backup</label>
                                <select id="backup_frequency" name="settings[backup_frequency]" class="form-control">
                                    <option value="daily" {{ ($settings['backup_frequency'] ?? 'weekly') == 'daily' ? 'selected' : '' }}>Diário</option>
                                    <option value="weekly" {{ ($settings['backup_frequency'] ?? 'weekly') == 'weekly' ? 'selected' : '' }}>Semanal</option>
                                    <option value="monthly" {{ ($settings['backup_frequency'] ?? 'weekly') == 'monthly' ? 'selected' : '' }}>Mensal</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="backup_retention">Número de backups a manter</label>
                                <input type="number" id="backup_retention" name="settings[backup_retention]" class="form-control"
                                       value="{{ $settings['backup_retention'] ?? 5 }}" min="1" max="30">
                                <small class="form-text text-muted">Backups mais antigos serão automaticamente excluídos quando novos forem criados.</small>
                            </div>
                        </div>
                    </div>

                    <div class="settings-section" style="background: var(--gray-100); border-left: 4px solid var(--primary-color); margin-bottom: 0;">
                        <h5 style="margin-top:0; color: var(--primary-color); font-size: 17px;">Ações de Backup Manual</h5>
                        <p style="margin-bottom: 18px; color: var(--text-light);">Você pode criar backups manualmente ou gerenciar os existentes.</p>
                        <div class="backup-actions">
                            <a href="{{ route('settings.backups') }}" class="btn btn-backup-dark">
                                <i class="fas fa-database"></i> Gerenciar Backups
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Configurações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- Sweet Alert 2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gerenciamento de abas
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabName = button.getAttribute('data-tab');


                tabButtons.forEach(tab => tab.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                button.classList.add('active');
                document.getElementById(`${tabName}-content`).classList.add('active');
            });
        });


        const emailNotificationsCheckbox = document.getElementById('enable_email_notifications');
        const emailSettings = document.getElementById('email-settings');

        if (emailNotificationsCheckbox) {
            emailNotificationsCheckbox.addEventListener('change', function() {
                emailSettings.style.display = this.checked ? 'block' : 'none';
            });
        }

        const autoBackupCheckbox = document.getElementById('enable_auto_backup');
        const backupSettings = document.getElementById('backup-settings');

        if (autoBackupCheckbox) {
            autoBackupCheckbox.addEventListener('change', function() {
                backupSettings.style.display = this.checked ? 'block' : 'none';
            });
        }
    });
</script>
@endpush
