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
        $tempSqlFile = storage_path('app/temp_backup.sql');
        $currentDbConfig = null;
        
        try {
            // Nome do arquivo de backup (data e hora)
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$timestamp}.zip";
            
            // Armazenar a conexão atual para possível reconexão posterior
            try {
                $currentDbConfig = config('database.connections.' . DB::getDefaultConnection());
            } catch (\Exception $e) {
                Log::warning("Não foi possível obter a configuração atual do banco de dados: " . $e->getMessage());
            }
            
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
                if (File::exists($tempSqlFile)) {
                    File::delete($tempSqlFile);
                }
                
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
            
            // Garantir que o arquivo temporário seja excluído em caso de erro
            if (File::exists($tempSqlFile)) {
                File::delete($tempSqlFile);
            }
            
            return ['success' => false, 'message' => $e->getMessage()];
        } finally {
            // Garantir que tenhamos uma conexão de banco de dados válida depois do backup
            if ($currentDbConfig) {
                try {
                    DB::disconnect();
                    DB::reconnect();
                } catch (\Exception $e) {
                    Log::error("Erro ao reconectar com o banco de dados: " . $e->getMessage());
                }
            }
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
            try {
                // Primeiro método: usar PHP para exportar diretamente do MySQL
                return $this->generateMySQLDumpWithPHP($config, $outputFile);
            } catch (\Exception $e) {
                Log::warning('Erro ao gerar dump com PHP: ' . $e->getMessage() . '. Tentando com mysqldump.');
                
                try {
                    // Segundo método: usar mysqldump comando externo
                    return $this->generateMySQLDumpWithCommand($config, $outputFile);
                } catch (\Exception $e) {
                    Log::error('Erro ao gerar dump com mysqldump: ' . $e->getMessage());
                    throw new \Exception('Erro ao gerar dump do banco de dados. Verifique se o mysqldump está instalado e as credenciais estão corretas.');
                }
            }
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
     * Gera dump do MySQL usando comando externo mysqldump
     *
     * @param array $config
     * @param string $outputFile
     * @return bool
     */
    protected function generateMySQLDumpWithCommand($config, $outputFile)
    {
        // Ajuste o comando para evitar problemas com senhas contendo caracteres especiais
        if (!empty($config['password'])) {
            // Utilizando MYSQL_PWD para evitar problemas com senhas na linha de comando
            putenv("MYSQL_PWD=" . $config['password']);
            $command = sprintf(
                'mysqldump -h %s -u %s %s > %s',
                escapeshellarg($config['host']),
                escapeshellarg($config['username']),
                escapeshellarg($config['database']),
                escapeshellarg($outputFile)
            );
        } else {
            $command = sprintf(
                'mysqldump -h %s -u %s %s > %s',
                escapeshellarg($config['host']),
                escapeshellarg($config['username']),
                escapeshellarg($config['database']),
                escapeshellarg($outputFile)
            );
        }
        
        // Adiciona porta se especificada
        if (!empty($config['port'])) {
            $command = str_replace('-h', '-h ' . escapeshellarg($config['host']) . ' -P ' . escapeshellarg($config['port']), $command);
        }
        
        // Executa o comando
        exec($command . " 2>&1", $output, $returnVar);
        
        // Limpa a variável de ambiente
        if (!empty($config['password'])) {
            putenv("MYSQL_PWD=");
        }
        
        if ($returnVar !== 0) {
            throw new \Exception(implode("\n", $output));
        }
        
        return true;
    }
    
    /**
     * Gera dump do MySQL usando PHP
     *
     * @param array $config
     * @param string $outputFile
     * @return bool
     */
    protected function generateMySQLDumpWithPHP($config, $outputFile)
    {
        try {
            // Verificar se podemos nos conectar ao banco de dados
            try {
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                // Se a conexão falhar, tente uma conexão direta com PDO
                return $this->generateMySQLDumpWithDirectPDO($config, $outputFile);
            }
            
            // Busca todas as tabelas
            $tables = DB::select('SHOW TABLES');
            $tablePrefix = "Tables_in_" . $config['database'];
            
            $output = "-- Backup gerado por Aluguel-Livros em " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
            $output .= "-- Database: " . $config['database'] . "\n\n";
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
            
            // Para cada tabela
            foreach ($tables as $table) {
                $tableName = $table->$tablePrefix;
                
                // Estrutura da tabela
                $createTableSql = DB::select("SHOW CREATE TABLE `$tableName`");
                $output .= "\n\n-- Estrutura da tabela `$tableName`\n";
                $output .= "DROP TABLE IF EXISTS `$tableName`;\n";
                
                // O segundo campo tem diferentes nomes dependendo da versão do MySQL
                $createStatement = isset($createTableSql[0]->{'Create Table'}) 
                    ? $createTableSql[0]->{'Create Table'} 
                    : $createTableSql[0]->{'Create View'};
                
                $output .= $createStatement . ";\n\n";
                
                // Dados da tabela
                $output .= "-- Dados da tabela `$tableName`\n";
                
                $rows = DB::table($tableName)->get();
                if (count($rows) > 0) {
                    // Preparar colunas
                    $columns = array_keys(get_object_vars($rows[0]));
                    $columnsList = '`' . implode('`, `', $columns) . '`';
                    
                    // Inserir dados
                    $output .= "INSERT INTO `$tableName` ($columnsList) VALUES\n";
                    
                    $rowData = [];
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($columns as $column) {
                            $value = $row->$column;
                            if (is_null($value)) {
                                $values[] = "NULL";
                            } else if (is_numeric($value)) {
                                $values[] = $value;
                            } else {
                                $values[] = "'" . str_replace("'", "\'", $value) . "'";
                            }
                        }
                        $rowData[] = "(" . implode(', ', $values) . ")";
                    }
                    $output .= implode(",\n", $rowData) . ";\n";
                }
            }
            
            $output .= "\nSET FOREIGN_KEY_CHECKS=1;\n";
            
            // Salvar o arquivo
            File::put($outputFile, $output);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao exportar banco de dados com PHP: ' . $e->getMessage());
            // Tentar o método PDO direto como última opção
            return $this->generateMySQLDumpWithDirectPDO($config, $outputFile);
        }
    }
    
    /**
     * Gera dump do MySQL usando PDO diretamente, sem depender do Laravel
     * Método alternativo quando outros métodos falham
     *
     * @param array $config
     * @param string $outputFile
     * @return bool
     */
    protected function generateMySQLDumpWithDirectPDO($config, $outputFile)
    {
        try {
            // Criar conexão PDO direta
            $dsn = "mysql:host={$config['host']};dbname={$config['database']}";
            if (!empty($config['port'])) {
                $dsn .= ";port={$config['port']}";
            }
            
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
            ];
            
            $pdo = new \PDO($dsn, $config['username'], $config['password'], $options);
            
            // Iniciar arquivo de saída
            $output = "-- Backup gerado por Aluguel-Livros (Método PDO) em " . date('Y-m-d H:i:s') . "\n";
            $output .= "-- Database: " . $config['database'] . "\n\n";
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            // Buscar todas as tabelas
            $tables = [];
            $stmt = $pdo->query('SHOW TABLES');
            while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
            
            foreach ($tables as $table) {
                // Estrutura da tabela
                $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
                $row = $stmt->fetch(\PDO::FETCH_NUM);
                $createTable = $row[1];
                
                $output .= "\n-- Estrutura da tabela `$table`\n";
                $output .= "DROP TABLE IF EXISTS `$table`;\n";
                $output .= $createTable . ";\n\n";
                
                // Contar registros
                $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
                $count = $stmt->fetchColumn();
                
                if ($count > 0) {
                    // Obter colunas
                    $stmt = $pdo->query("SELECT * FROM `$table` LIMIT 1");
                    $columnCount = $stmt->columnCount();
                    $columns = [];
                    
                    for ($i = 0; $i < $columnCount; $i++) {
                        $columnMeta = $stmt->getColumnMeta($i);
                        $columns[] = $columnMeta['name'];
                    }
                    
                    $columnNames = '`' . implode('`, `', $columns) . '`';
                    
                    // Escrevemos o cabeçalho do INSERT
                    $output .= "-- Dados da tabela `$table`\n";
                    $output .= "INSERT INTO `$table` ($columnNames) VALUES\n";
                    
                    // Buscar dados em lotes para evitar problemas de memória
                    $batchSize = 100;
                    $batches = ceil($count / $batchSize);
                    $rowData = [];
                    
                    for ($b = 0; $b < $batches; $b++) {
                        $stmt = $pdo->query("SELECT * FROM `$table` LIMIT " . ($b * $batchSize) . ", $batchSize");
                        
                        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                            $values = [];
                            
                            foreach ($columns as $column) {
                                $value = $row[$column];
                                
                                if (is_null($value)) {
                                    $values[] = "NULL";
                                } else if (is_numeric($value)) {
                                    $values[] = $value;
                                } else {
                                    $values[] = "'" . str_replace("'", "\'", $value) . "'";
                                }
                            }
                            
                            $rowData[] = "(" . implode(', ', $values) . ")";
                        }
                    }
                    
                    $output .= implode(",\n", $rowData) . ";\n";
                }
            }
            
            $output .= "\nSET FOREIGN_KEY_CHECKS=1;\n";
            
            // Salvar o arquivo
            File::put($outputFile, $output);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao exportar banco de dados com PDO direto: ' . $e->getMessage());
            throw new \Exception('Não foi possível gerar o backup do banco de dados após tentar múltiplos métodos. Erro: ' . $e->getMessage());
        }
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