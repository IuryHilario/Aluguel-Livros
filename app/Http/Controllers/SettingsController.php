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
}