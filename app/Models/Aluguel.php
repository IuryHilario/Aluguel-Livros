<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Settings;

class Aluguel extends Model
{
    use HasFactory;

    protected $table = 'aluguel';
    protected $primaryKey = 'id_aluguel';
    
    const STATUS_ATIVO = 'Ativo';
    const STATUS_ATRASADO = 'Atrasado';
    const STATUS_DEVOLVIDO = 'Devolvido';
    const DIAS_ATRASO = 14;
    
    protected $fillable = [
        'id_usuario',
        'id_livro',
        'dt_aluguel',
        'dt_devolucao',
        'dt_devolucao_efetiva',
        'ds_status'
    ];

    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
    
    public function livro()
    {
        return $this->belongsTo(Livro::class, 'id_livro', 'id_livro');
    }
    
    public function renovacoes()
    {
        return $this->hasMany(Renovacao::class, 'id_aluguel', 'id_aluguel');
    }
    
    public function getUltimaRenovacaoAttribute()
    {
        $ultimaRenovacao = $this->renovacoes()->latest('dt_renovacao')->first();
        return $ultimaRenovacao ? $ultimaRenovacao->dt_renovacao : null;
    }
    
    public function isAtrasado()
    {
        if ($this->ds_status === self::STATUS_DEVOLVIDO) {
            return false;
        }
        
        $dataDevolucao = Carbon::parse($this->dt_devolucao);
        $hoje = Carbon::now()->startOfDay();
        
        return $dataDevolucao->lt($hoje);
    }

    public function diasAtraso()
    {
        if (!$this->isAtrasado()) {
            return 0;
        }
        
        $dataDevolucao = Carbon::parse($this->dt_devolucao)->startOfDay();
        $hoje = Carbon::now()->startOfDay();
        
        return $dataDevolucao->diffInDays($hoje);
    }
    
    public function atualizaStatusAtrasado()
    {
        if ($this->ds_status === self::STATUS_DEVOLVIDO) {
            return $this;
        }
        
        $dataDevolucao = Carbon::parse($this->dt_devolucao);
        $hoje = Carbon::now()->startOfDay();
        
        if ($dataDevolucao->lt($hoje)) {
            if ($this->ds_status !== self::STATUS_ATRASADO) {
                $this->ds_status = self::STATUS_ATRASADO;
                $this->save();
            }
        } 
        else {
            if ($this->ds_status !== self::STATUS_ATIVO) {
                $this->ds_status = self::STATUS_ATIVO;
                $this->save();
            }
        }
        
        return $this;
    }
    
    public function estaAtrasado()
    {
        if ($this->dt_devolucao_efetiva) {
            return false;
        }
        
        $dataDevolucao = Carbon::parse($this->dt_devolucao)->startOfDay();
        $hoje = Carbon::now()->startOfDay();
        
        return $dataDevolucao->lt($hoje) && $this->ds_status != 'Devolvido';
    }
    
    public function atualizaStatus()
    {
        if ($this->estaAtrasado() && $this->ds_status != 'Atrasado') {
            $this->ds_status = 'Atrasado';
            $this->save();
        }
    }
    
    public function podeRenovar()
    {
        $settings = Settings::getAllSettings();

        if ($this->ds_status === self::STATUS_DEVOLVIDO) {
            return false;
        }
        
        $maxRenovacoes = $settings['max_loans_per_user'] ?? 2;
        
        if ($this->nu_renovacoes >= $maxRenovacoes) {
            return false;
        }
        
        $allowRenewalWithPending =  $settings['allow_renewal_with_pending'] ?? false;
        
        if (!$allowRenewalWithPending) {
            $usuario = $this->usuario;
            
            $outrosEmprestimosAtrasados = $usuario->alugueis()
                ->where('id_aluguel', '!=', $this->id_aluguel)
                ->where('ds_status', 'Atrasado')
                ->exists();
                
            if ($outrosEmprestimosAtrasados) {
                return false;
            }
            
            if ($this->isAtrasado() && $this->diasAtraso() > self::DIAS_ATRASO) {
                return false;
            }
        } else {
            if ($this->isAtrasado() && $this->diasAtraso() > self::DIAS_ATRASO) {
                return false;
            }
        }
        
        return true;
    }
}