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
            
            foreach ($settings as $key => $value) {
                Settings::updateOrCreateSetting($key, $value);
            }
            
            Settings::applySettings($settings);
            
            return redirect()->route('settings.index')->with('success', 'Configurações atualizadas com sucesso!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar configurações: ' . $e->getMessage());
        }
    }    
}