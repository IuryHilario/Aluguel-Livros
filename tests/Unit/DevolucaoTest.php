<?php

namespace Tests\Unit;

use App\Models\Aluguel;
use App\Models\Usuario;
use App\Models\Livro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevolucaoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pode_devolver_livro_e_atualizar_status()
    {
        $usuario = Usuario::create([
            'nome' => 'Devolução',
            'email' => 'devolucao@email.com',
            'telefone' => '111111111',
        ]);
        $livro = Livro::create([
            'titulo' => 'Livro Devolução',
            'autor' => 'Autor',
            'editor' => 'Editora',
            'ano_publicacao' => 2024,
            'quantidade' => 1,
        ]);
        $aluguel = Aluguel::create([
            'id_usuario' => $usuario->id_usuario,
            'id_livro' => $livro->id_livro,
            'dt_aluguel' => now()->toDateString(),
            'dt_devolucao' => now()->addDays(7)->toDateString(),
            'ds_status' => Aluguel::STATUS_ATIVO,
        ]);
        $aluguel->ds_status = Aluguel::STATUS_DEVOLVIDO;
        $aluguel->dt_devolucao_efetiva = now()->toDateString();
        $aluguel->save();
        $this->assertDatabaseHas('aluguel', [
            'id_aluguel' => $aluguel->id_aluguel,
            'ds_status' => Aluguel::STATUS_DEVOLVIDO,
        ]);
    }
}
