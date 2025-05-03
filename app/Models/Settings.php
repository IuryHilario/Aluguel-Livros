<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Settings extends Model
{
    protected $table = 'configuracoes';
    protected $primaryKey = 'chave';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'chave', 'valor'
    ];

    public static $booleanSettings = [
        'show_book_covers', 
        'enable_email_notifications', 
        'send_overdue_notices',
        'enable_auto_backup',
        'allow_renewal_with_pending'
    ];

    public static function getAllSettings()
    {
        $settings = DB::table('configuracoes')->get();
        $settingsArray = [];
        
        foreach ($settings as $setting) {
            if (in_array($setting->chave, self::$booleanSettings)) {
                $settingsArray[$setting->chave] = (bool)$setting->valor;
            } else {
                $settingsArray[$setting->chave] = $setting->valor;
            }
        }
        
        return $settingsArray;
    }

    public static function updateOrCreateSetting($key, $value)
    {
        if (in_array($key, self::$booleanSettings)) {
            $value = (int)(bool)$value;
        }
        
        return DB::table('configuracoes')
            ->updateOrInsert(
                ['chave' => $key],
                ['valor' => $value, 'updated_at' => now()]
            );
    }

    public static function applySettings($settings)
    {
        if (isset($settings['items_per_page'])) {
            config(['app.items_per_page' => $settings['items_per_page']]);
        }
        
        if (isset($settings['email_from_name'])) {
            config(['mail.from.name' => $settings['email_from_name']]);
        }
        
        if (isset($settings['email_from_address'])) {
            config(['mail.from.address' => $settings['email_from_address']]);
        }
    }
}
