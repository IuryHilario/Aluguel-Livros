<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Livro extends Model
{
    use HasFactory;

    protected $table = 'livro';
    protected $primaryKey = 'id_livro';
    
    protected $fillable = [
        'titulo',
        'autor',
        'editor',
        'ano_publicacao',
        'capa',
        'quantidade'
    ];

    protected $hidden = ['capa'];

    protected $appends = ['has_capa'];

    public function alugueis()
    {
        return $this->hasMany(Aluguel::class, 'id_livro', 'id_livro');
    }
    
    public function disponivel()
    {
        return $this->quantidade > 0;
    }
    
    public function alugueisAtivos()
    {
        return $this->alugueis()
            ->whereIn('ds_status', ['Ativo', 'Atrasado'])
            ->count();
    }

    public function getHasCapaAttribute()
    {
        return !empty($this->capa);
    }

    public function getQuantidadeDisponivelAttribute()
    {
        $alugueisAtivos = $this->alugueisAtivos();
            
        return $this->quantidade - $alugueisAtivos;
    }
    
    public static function getLivrosPopulares($limit = 4)
    {
        return self::select('livro.*', DB::raw('COUNT(aluguel.id_aluguel) as total_alugueis'))
            ->leftJoin('aluguel', 'livro.id_livro', '=', 'aluguel.id_livro')
            ->groupBy('livro.id_livro', 'livro.titulo', 'livro.autor', 'livro.editor', 'livro.ano_publicacao', 'livro.capa', 'livro.quantidade', 'livro.created_at', 'livro.updated_at')
            ->orderBy('total_alugueis', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($livro) {
            if ($livro->quantidade < 0) {
                throw new \InvalidArgumentException('A quantidade não pode ser negativa.');
            }
        });
        static::updating(function ($livro) {
            if ($livro->quantidade < 0) {
                throw new \InvalidArgumentException('A quantidade não pode ser negativa.');
            }
        });
    }
}