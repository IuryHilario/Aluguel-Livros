<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;
use Illuminate\Support\Facades\Log;

class RunScheduledBackups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa o backup automático do sistema de acordo com a configuração';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando configurações de backup automático...');
        
        $backupService = new BackupService();
        $result = $backupService->runScheduledBackup();
        
        if ($result === false) {
            $this->info('Backup automático está desativado ou não programado para hoje.');
            return 0;
        }
        
        if ($result['success']) {
            $this->info('Backup criado com sucesso: ' . $result['filename']);
            $this->info('Tamanho: ' . $result['size']);
            Log::info('Backup automático criado com sucesso: ' . $result['filename']);
            return 0;
        } else {
            $this->error('Erro ao criar backup: ' . ($result['message'] ?? 'Erro desconhecido'));
            Log::error('Erro ao criar backup automático: ' . ($result['message'] ?? 'Erro desconhecido'));
            return 1;
        }
    }
}