<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Renovacao extends Model
{
    use HasFactory;

    protected $table = 'renovacao';
    protected $primaryKey = 'id_renovacao';
    
    protected $fillable = [
        'id_aluguel',
        'dt_renovacao',
        'dt_devolucao_nova',
        'ds_status'
    ];

    // Relacionamento com aluguel
    public function aluguel()
    {
        return $this->belongsTo(Aluguel::class, 'id_aluguel', 'id_aluguel');
    }
}