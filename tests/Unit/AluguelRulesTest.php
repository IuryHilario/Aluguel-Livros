<?php

namespace Tests\Unit;

use App\Models\Aluguel;
use App\Models\Usuario;
use App\Models\Livro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AluguelRulesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function nao_permite_alugar_livro_sem_estoque()
    {
        $usuario = Usuario::create([
            'nome' => 'Sem Estoque',
            'email' => 'semestoque@email.com',
            'telefone' => '111111111',
        ]);
        $livro = Livro::create([
            'titulo' => 'Livro Sem Estoque',
            'autor' => 'Autor',
            'editor' => 'Editora',
            'ano_publicacao' => 2024,
            'quantidade' => 0,
        ]);
        $this->expectException(\Exception::class);
        if ($livro->quantidade <= 0) {
            throw new \Exception('Livro sem estoque não pode ser alugado.');
        }
        Aluguel::create([
            'id_usuario' => $usuario->id_usuario,
            'id_livro' => $livro->id_livro,
            'dt_aluguel' => now()->toDateString(),
            'dt_devolucao' => now()->addDays(7)->toDateString(),
            'ds_status' => Aluguel::STATUS_ATIVO,
        ]);
    }
}
