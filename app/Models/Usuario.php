<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Usuario extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'usuario';
    
    protected $primaryKey = 'id_usuario';
    
    protected $fillable = [
        'nome',
        'email',
        'telefone'
    ];

    public function alugueis()
    {
        return $this->hasMany(Aluguel::class, 'id_usuario', 'id_usuario');
    }
    
    public function contaAlugueisAtivos()
    {
        return $this->alugueis()
            ->whereIn('ds_status', ['Ativo', 'Atrasado'])
            ->count();
    }
    
    public function temAtrasos()
    {
        return $this->alugueis()
            ->where('ds_status', 'Atrasado')
            ->count() > 0;
    }

    public function routeNotificationForMail()
    {
        return $this->email;
    }
}