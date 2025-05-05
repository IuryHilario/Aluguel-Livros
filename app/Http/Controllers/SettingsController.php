<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Settings;
use Carbon\Carbon;
use Exception;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Settings::getAllSettings();
        return view('settings.index', compact('settings'));
    }
    
    public function update(Request $request)
    {
        try {
            $settings = $request->input('settings', []);
            
            foreach (Settings::$booleanSettings as $key) {
                if (isset($settings[$key])) {
                    $settings[$key] = (bool)$settings[$key];
                }
            }
            
            foreach ($settings as $key => $value) {
                Settings::updateOrCreateSetting($key, $value);
            }
            
            Settings::applySettings($settings);
            
            if (isset($settings['email_from_name']) || isset($settings['email_from_address'])) {
                config([
                    'mail.from.name' => $settings['email_from_name'] ?? config('mail.from.name'),
                    'mail.from.address' => $settings['email_from_address'] ?? config('mail.from.address')
                ]);
            }
            
            return redirect()->route('settings.index')->with('success', 'Configurações atualizadas com sucesso!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar configurações: ' . $e->getMessage());
        }
    }    
    
    public function backups()
    {
        $backupService = new \App\Services\BackupService();
        $backups = $backupService->getBackups();
        
        return view('settings.backups', compact('backups'));
    }
    
    public function createBackup()
    {
        try {
            $backupService = new \App\Services\BackupService();
            $result = $backupService->createBackup();
            
            if ($result['success']) {
                return redirect()->route('settings.backups')
                    ->with('success', 'Backup criado com sucesso!');
            } else {
                return redirect()->route('settings.backups')
                    ->with('error', 'Erro ao criar backup: ' . ($result['message'] ?? 'Erro desconhecido'));
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao criar backup: ' . $e->getMessage());
            return redirect()->route('settings.backups')
                ->with('error', 'Erro ao criar backup: ' . $e->getMessage());
        }
    }
    
    public function downloadBackup($filename)
    {
        $backupPath = storage_path('app/backups/' . $filename);
        
        if (file_exists($backupPath)) {
            return response()->download($backupPath);
        }
        
        return redirect()->route('settings.backups')
            ->with('error', 'Arquivo de backup não encontrado.');
    }
    
    public function deleteBackup($filename)
    {
        try {
            $backupService = new \App\Services\BackupService();
            $result = $backupService->deleteBackup($filename);
            
            if ($result) {
                return redirect()->route('settings.backups')
                    ->with('success', 'Backup excluído com sucesso!');
            } else {
                return redirect()->route('settings.backups')
                    ->with('error', 'Arquivo de backup não encontrado.');
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir backup: ' . $e->getMessage());
            return redirect()->route('settings.backups')
                ->with('error', 'Erro ao excluir backup: ' . $e->getMessage());
        }
    }
}