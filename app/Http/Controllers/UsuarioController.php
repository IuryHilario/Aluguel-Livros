<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Models\Settings;

class UsuarioController extends Controller
{

    public function index(Request $request)
    {
        $query = Usuario::query();
        $settings = Settings::getAllSettings();
        
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nome', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('telefone', 'like', '%' . $searchTerm . '%');
            });
        }
        
        $orderBy = $request->order_by ?? 'id_usuario';
        $orderDir = $request->order_dir ?? 'DESC';
        
        $query->orderBy($orderBy, $orderDir);
        
        $usuarios = $query->paginate($settings['items_per_page']);
        
        $usuarios->appends($request->all());
        
        return view('users.index', compact('usuarios'));
    }

    public function create()
    {
        $settings = Settings::getAllSettings();
        return view('users.create', compact('settings'));
    }

    public function store(Request $request)
    {
        $settings = Settings::getAllSettings();
        $max_loans = $settings['max_loans_per_user'] ?? 3;
        
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:usuario,email',
            'telefone' => 'nullable|string|max:20',
            'max_emprestimos' => 'nullable|integer|min:1|max:' . $max_loans,
        ], [
            'nome.required' => 'O nome é obrigatório',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'Informe um e-mail válido',
            'email.unique' => 'Este e-mail já está cadastrado',
            'max_emprestimos.max' => 'O número máximo de empréstimos não pode exceder ' . $max_loans,
        ]);

        if (!isset($validated['max_emprestimos'])) {
            $validated['max_emprestimos'] = $max_loans;
        }

        Usuario::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function show($id)
    {
        $usuario = Usuario::findOrFail($id);
        $settings = Settings::getAllSettings();
        return view('users.show', compact('usuario', 'settings'));
    }

    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        $settings = Settings::getAllSettings();
        return view('users.edit', compact('usuario', 'settings'));
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        $settings = Settings::getAllSettings();
        $max_loans = $settings['max_loans_per_user'] ?? 3;
        
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:usuario,email,'.$id.',id_usuario',
            'telefone' => 'nullable|string|max:20',
            'max_emprestimos' => 'nullable|integer|min:1|max:' . $max_loans,
        ], [
            'nome.required' => 'O nome é obrigatório',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'Informe um e-mail válido',
            'email.unique' => 'Este e-mail já está cadastrado',
            'max_emprestimos.max' => 'O número máximo de empréstimos não pode exceder ' . $max_loans,
        ]);

        $usuario->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuário removido com sucesso!');
    }
}