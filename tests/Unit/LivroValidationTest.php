<?php

namespace Tests\Unit;

use App\Models\Livro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LivroValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function nao_permite_quantidade_negativa()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        Livro::create([
            'titulo' => 'Livro Inválido',
            'autor' => 'Autor',
            'editor' => 'Editora',
            'ano_publicacao' => 2024,
            'quantidade' => -1,
        ]);
    }
}
