<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Livro;
use App\Models\Usuario;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'livros' => [],
                'usuarios' => []
            ]);
        }
        
        // Buscar livros correspondentes
        $livros = Livro::where('titulo', 'LIKE', "%{$query}%")
                      ->orWhere('autor', 'LIKE', "%{$query}%")
                      ->select('id_livro as id', 'titulo', 'autor', 'quantidade')
                      ->limit(5)
                      ->get();
        
        // Adicionar informação de disponibilidade
        foreach ($livros as $livro) {
            $livro->disponivel = $livro->quantidade > 0;
        }
        
        // Buscar usuários correspondentes
        $usuarios = Usuario::where('nome', 'LIKE', "%{$query}%")
                          ->orWhere('email', 'LIKE', "%{$query}%")
                          ->select('id_usuario as id', 'nome', 'email')
                          ->limit(5)
                          ->get();
        
        return response()->json([
            'livros' => $livros,
            'usuarios' => $usuarios
        ]);
    }
}