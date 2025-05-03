<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique();
            $table->text('valor')->nullable();
            $table->timestamps();
        });
        
        // Inserir configurações padrão
        $this->seedDefaultSettings();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracoes');
    }
    
    /**
     * Alimenta a tabela com as configurações padrão
     */
    private function seedDefaultSettings(): void
    {
        $defaultSettings = [
            // Configurações gerais
            'system_name' => 'Aluga Livros',
            'admin_email' => 'admin@alugalivros.com',
            'contact_phone' => '',
            'show_book_covers' => 1,
            'items_per_page' => 10,
            
            // Empréstimos
            'default_loan_period' => 14,
            'max_loans_per_user' => 3,
            'late_fee_per_day' => 0.50,
            'max_renewals' => 2,
            'allow_renewal_with_pending' => 0,
            
            // Notificações
            'enable_email_notifications' => 0,
            'email_from_name' => 'Aluga Livros',
            'email_from_address' => 'noreply@alugalivros.com',
            'days_before_due_reminder' => 2,
            'send_overdue_notices' => 1,
            'overdue_notice_frequency' => 3,
            
            // Backup
            'enable_auto_backup' => 0,
            'backup_frequency' => 'weekly',
            'backup_retention' => 5,
        ];
        
        $now = now();
        
        $settings = [];
        foreach ($defaultSettings as $key => $value) {
            $settings[] = [
                'chave' => $key,
                'valor' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        DB::table('configuracoes')->insert($settings);
    }
};