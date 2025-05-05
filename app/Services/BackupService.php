<?php

namespace App\Services;

use ZipArchive;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Settings;

class BackupService
{
    protected $backupPath;
    protected $settings;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        $this->settings = Settings::getAllSettings();
        
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Cria um backup do banco de dados e arquivos importantes
     *
     * @return array
     */
    public function createBackup()
    {
        try {
            // Nome do arquivo de backup (data e hora)
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$timestamp}.zip";
            $tempSqlFile = storage_path('app/temp_backup.sql');
            
            // Gerar arquivo SQL com dump do banco de dados
            $this->generateDatabaseDump($tempSqlFile);
            
            // Criar arquivo ZIP
            $zip = new ZipArchive();
            $zipPath = $this->backupPath . '/' . $filename;
            
            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                // Adicionar arquivo SQL
                $zip->addFile($tempSqlFile, 'database.sql');
                
                // Adicionar arquivos importantes (uploads, configurações, etc)
                $this->addImportantFilesToZip($zip);
                
                $zip->close();
                
                // Remover arquivo SQL temporário
                File::delete($tempSqlFile);
                
                // Limpar backups antigos
                $this->cleanOldBackups();
                
                return [
                    'success' => true,
                    'filename' => $filename,
                    'path' => $zipPath,
                    'size' => $this->formatFileSize(File::size($zipPath)),
                    'created_at' => Carbon::now()->format('d/m/Y H:i:s')
                ];
            } else {
                Log::error('Não foi possível criar o arquivo ZIP de backup');
                return ['success' => false, 'message' => 'Erro ao criar o arquivo ZIP'];
            }
        } catch (\Exception $e) {
            Log::error('Erro ao criar backup: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Gera um dump do banco de dados
     *
     * @param string $outputFile
     * @return bool
     */
    protected function generateDatabaseDump($outputFile)
    {
        $connection = DB::getDefaultConnection();
        $config = config("database.connections.{$connection}");
        
        // Para MySQL/MariaDB
        if ($config['driver'] === 'mysql') {
            $command = sprintf(
                'mysqldump -h %s -u %s %s %s > %s',
                escapeshellarg($config['host']),
                escapeshellarg($config['username']),
                !empty($config['password']) ? '-p' . escapeshellarg($config['password']) : '',
                escapeshellarg($config['database']),
                escapeshellarg($outputFile)
            );
            
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception('Erro ao gerar dump do banco de dados. Verifique se o mysqldump está instalado.');
            }
            
            return true;
        } 
        // SQLite
        elseif ($config['driver'] === 'sqlite') {
            $dbFile = database_path(basename($config['database']));
            File::copy($dbFile, $outputFile);
            return true;
        }
        
        throw new \Exception('O driver de banco de dados não é suportado para backup: ' . $config['driver']);
    }
    
    /**
     * Adiciona arquivos importantes ao ZIP
     *
     * @param ZipArchive $zip
     * @return void
     */
    protected function addImportantFilesToZip($zip)
    {
        // Adicionar pastas de uploads
        $this->addDirectoryToZip($zip, public_path('uploads'), 'uploads');
        
        // Adicionar arquivos de configuração
        $envFile = base_path('.env');
        if (File::exists($envFile)) {
            $zip->addFile($envFile, '.env');
        }
    }
    
    /**
     * Adiciona um diretório inteiro ao arquivo ZIP
     *
     * @param ZipArchive $zip
     * @param string $sourceDir
     * @param string $zipDir
     * @return void
     */
    protected function addDirectoryToZip($zip, $sourceDir, $zipDir)
    {
        if (!File::exists($sourceDir)) {
            return;
        }
        
        $files = File::allFiles($sourceDir);
        
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = $zipDir . '/' . substr($filePath, strlen($sourceDir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
    
    /**
     * Remove backups antigos com base na configuração de retenção
     *
     * @return void
     */
    public function cleanOldBackups()
    {
        $retention = $this->settings['backup_retention'] ?? 5;
        
        $backups = $this->getBackups();
        
        // Se temos mais backups que o limite configurado
        if (count($backups) > $retention) {
            // Ordenar por data de criação (mais antigo primeiro)
            usort($backups, function ($a, $b) {
                return $a['created_at_timestamp'] <=> $b['created_at_timestamp'];
            });
            
            // Remover os backups mais antigos
            $backupsToRemove = array_slice($backups, 0, count($backups) - $retention);
            
            foreach ($backupsToRemove as $backup) {
                $this->deleteBackup($backup['filename']);
            }
        }
    }
    
    /**
     * Obtém a lista de backups disponíveis
     *
     * @return array
     */
    public function getBackups()
    {
        $backups = [];
        
        if (!File::exists($this->backupPath)) {
            return $backups;
        }
        
        $files = File::files($this->backupPath);
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $filename = basename($file);
                $created = File::lastModified($file);
                $createdCarbon = Carbon::createFromTimestamp($created);
                
                $backups[] = [
                    'filename' => $filename,
                    'path' => $file,
                    'size' => $this->formatFileSize(File::size($file)),
                    'created_at' => $createdCarbon->format('d/m/Y H:i:s'),
                    'created_at_timestamp' => $created
                ];
            }
        }
        
        // Ordenar por data de criação (mais recente primeiro)
        usort($backups, function ($a, $b) {
            return $b['created_at_timestamp'] <=> $a['created_at_timestamp'];
        });
        
        return $backups;
    }
    
    /**
     * Exclui um backup
     *
     * @param string $filename
     * @return bool
     */
    public function deleteBackup($filename)
    {
        $filePath = $this->backupPath . '/' . $filename;
        
        if (File::exists($filePath)) {
            File::delete($filePath);
            return true;
        }
        
        return false;
    }
    
    /**
     * Formata o tamanho do arquivo para exibição
     *
     * @param int $bytes
     * @return string
     */
    protected function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Realiza o backup automático de acordo com a programação
     *
     * @return bool|array
     */
    public function runScheduledBackup()
    {
        // Verifica se o backup automático está ativado
        if (!isset($this->settings['enable_auto_backup']) || !$this->settings['enable_auto_backup']) {
            return false;
        }
        
        $frequency = $this->settings['backup_frequency'] ?? 'weekly';
        
        // Verifica se é hora de executar o backup de acordo com a frequência
        if (!$this->shouldRunBackup($frequency)) {
            return false;
        }
        
        // Executa o backup
        return $this->createBackup();
    }
    
    /**
     * Verifica se o backup deve ser executado hoje
     *
     * @param string $frequency
     * @return bool
     */
    protected function shouldRunBackup($frequency)
    {
        $now = Carbon::now();
        
        switch ($frequency) {
            case 'daily':
                return true; // Todo dia
                
            case 'weekly':
                return $now->dayOfWeek === Carbon::SUNDAY; // Aos domingos
                
            case 'monthly':
                return $now->day === 1; // No primeiro dia do mês
                
            default:
                return false;
        }
    }
}