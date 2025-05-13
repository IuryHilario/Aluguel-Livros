<?php

namespace Tests\Unit;

use App\Models\Aluguel;
use App\Models\Usuario;
use App\Models\Livro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AluguelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pode_inserir_aluguel()
    {
        $usuario = Usuario::create([
            'nome' => 'Maria Teste',
            'email' => 'maria@email.com',
            'telefone' => '11988887777',
        ]);

        $livro = Livro::create([
            'titulo' => 'Livro Aluguel',
            'autor' => 'Autor Aluguel',
            'editor' => 'Editora Aluguel',
            'ano_publicacao' => 2023,
            'quantidade' => 2,
        ]);

        $aluguel = Aluguel::create([
            'id_usuario' => $usuario->id_usuario,
            'id_livro' => $livro->id_livro,
            'dt_aluguel' => now()->toDateString(),
            'dt_devolucao' => now()->addDays(7)->toDateString(),
            'ds_status' => Aluguel::STATUS_ATIVO,
        ]);

        $this->assertDatabaseHas('aluguel', [
            'id_usuario' => $usuario->id_usuario,
            'id_livro' => $livro->id_livro,
            'ds_status' => Aluguel::STATUS_ATIVO,
        ]);
    }
}
