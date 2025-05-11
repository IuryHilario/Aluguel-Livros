<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set('America/Sao_Paulo');
        \Carbon\Carbon::setLocale('pt_BR');

        // Compartilha as configurações com todas as views
        View::composer('*', function ($view) {
            $settings = $this->getAllSettings();
            $view->with('settings', $settings);
        });
    }

    /**
     * Recupera todas as configurações como um array associativo
     */
    private function getAllSettings()
    {
        // Verifica se a tabela existe para evitar erros durante migrações
        if (!Schema::hasTable('configuracoes')) {
            return [];
        }

        $settings = DB::table('configuracoes')->get();
        
        // Converte a coleção de objetos em um array associativo
        $settingsArray = [];
        
        foreach ($settings as $setting) {
            // Converte valores booleanos
            if (in_array($setting->chave, [
                'show_book_covers', 
                'enable_email_notifications', 
                'send_overdue_notices',
                'enable_auto_backup',
                'allow_renewal_with_pending'
            ])) {
                $settingsArray[$setting->chave] = (bool)$setting->valor;
            } else {
                $settingsArray[$setting->chave] = $setting->valor;
            }
        }
        
        return $settingsArray;
    }
}
