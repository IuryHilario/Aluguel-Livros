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
        
        if (isset($settings['enable_email_notifications'])) {
            config(['app.enable_email_notifications' => (bool)$settings['enable_email_notifications']]);
        }
        
        if (isset($settings['days_before_due_reminder'])) {
            config(['app.days_before_due_reminder' => $settings['days_before_due_reminder']]);
        }
        
        if (isset($settings['send_overdue_notices'])) {
            config(['app.send_overdue_notices' => (bool)$settings['send_overdue_notices']]);
        }
        
        if (isset($settings['overdue_notice_frequency'])) {
            config(['app.overdue_notice_frequency' => $settings['overdue_notice_frequency']]);
        }
    }
}
