<?php

namespace Tests\Unit;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function nao_permite_email_duplicado()
    {
        Usuario::create([
            'nome' => 'Usuário 1',
            'email' => 'email@teste.com',
            'telefone' => '111111111',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Usuario::create([
            'nome' => 'Usuário 2',
            'email' => 'email@teste.com',
            'telefone' => '222222222',
        ]);
    }

    /** @test */
    public function valida_campos_obrigatorios_usuario()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Usuario::create([
            'email' => 'semnome@teste.com',
        ]);
    }
}
