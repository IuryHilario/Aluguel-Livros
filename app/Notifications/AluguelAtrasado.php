<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Aluguel;
use App\Models\Settings;
use Carbon\Carbon;

class AluguelAtrasado extends Notification
{
    use Queueable;

    protected $aluguel;
    protected $diasAtraso;
    protected $settings;

    /**
     * Create a new notification instance.
     */
    public function __construct(Aluguel $aluguel)
    {
        $this->aluguel = $aluguel;
        $this->diasAtraso = $aluguel->diasAtraso();
        $this->settings = Settings::getAllSettings();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Certifica que temos as informações necessárias
        if (!$this->aluguel->relationLoaded('livro')) {
            $this->aluguel->load('livro');
        }
        
        $livroTitulo = $this->aluguel->livro->titulo;
        $dataEmprestimo = Carbon::parse($this->aluguel->dt_aluguel)->format('d/m/Y');
        $dataDevolucao = Carbon::parse($this->aluguel->dt_devolucao)->format('d/m/Y');
        
        $systemName = $this->settings['system_name'] ?? 'Aluga Livros';

        return (new MailMessage)
            ->subject("{$systemName} - Devolução de livro em atraso")
            ->greeting("Olá, {$notifiable->nome}!")
            ->line("Notamos que o empréstimo do livro \"{$livroTitulo}\" está em atraso.")
            ->line("Data do empréstimo: {$dataEmprestimo}")
            ->line("Data prevista para devolução: {$dataDevolucao}")
            ->line("Dias em atraso: {$this->diasAtraso}")
            ->line('Por favor, devolva o livro o quanto antes para evitar penalidades.')
            ->line('Obrigado por utilizar nossa biblioteca!')
            ->salutation("Atenciosamente, Equipe {$systemName}");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id_aluguel' => $this->aluguel->id_aluguel,
            'livro' => $this->aluguel->livro->titulo,
            'data_devolucao' => $this->aluguel->dt_devolucao,
            'dias_atraso' => $this->diasAtraso
        ];
    }
}
