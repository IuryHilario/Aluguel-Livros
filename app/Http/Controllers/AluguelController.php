<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aluguel;
use App\Models\Livro;
use App\Models\Usuario;
use App\Models\Settings;
use App\Models\Renovacao;
use Carbon\Carbon;
use DB;

class AluguelController extends Controller
{
    public function index(Request $request)
    {
        $settings = Settings::getAllSettings();
        
        $query = Aluguel::with(['usuario', 'livro'])
            ->whereIn('ds_status', [Aluguel::STATUS_ATIVO, Aluguel::STATUS_ATRASADO]);
        
        if ($request->filled('user')) {
            $query->whereHas('usuario', function($q) use ($request) {
                $q->where('nome', 'LIKE', '%' . $request->user . '%');
            });
        }
        
        if ($request->filled('book')) {
            $query->whereHas('livro', function($q) use ($request) {
                $q->where('titulo', 'LIKE', '%' . $request->book . '%');
            });
        }
        
        if ($request->filled('status')) {
            $query->where('ds_status', $request->status);
        }
        
        if ($request->filled('start_date')) {
            $query->where('dt_aluguel', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('dt_aluguel', '<=', $request->end_date);
        }
        
        $alugueis = $query->orderBy('id_aluguel', 'desc')->get();
            
        foreach ($alugueis as $aluguel) {
            $aluguel->atualizaStatusAtrasado();
        }

        $alugueis = $query->paginate($settings['items_per_page'])->appends($request->query());
        
        return view('rentals.index', compact('alugueis'));
    }

    public function create()
    {
        $livros = Livro::where('quantidade', '>', 0)->get();
        $usuarios = Usuario::orderBy('nome')->get();
        
        $settings = Settings::getAllSettings();
        $defaultLoanPeriod = $settings['default_loan_period'] ?? 14;
        
        return view('rentals.create', compact('livros', 'usuarios', 'settings', 'defaultLoanPeriod'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuario,id_usuario',
            'id_livro' => 'required|exists:livro,id_livro',
            'dt_aluguel' => 'required|date',
            'dt_devolucao' => 'required|date|after_or_equal:dt_aluguel',
        ]);
        
        $livro = Livro::findOrFail($request->id_livro);
        if (!$livro->disponivel()) {
            return redirect()->back()->with('error', 'Livro indisponível para aluguel.');
        }
        
        $settings = Settings::getAllSettings();
        $maxLoanPerUser = $settings['max_loans_per_user'] ?? 3;
        
        $activeLoansCount = Aluguel::where('id_usuario', $request->id_usuario)
            ->whereIn('ds_status', [Aluguel::STATUS_ATIVO, Aluguel::STATUS_ATRASADO])
            ->count();
            
        if ($activeLoansCount >= $maxLoanPerUser) {
            return redirect()->back()->with('error', "Este usuário já atingiu o limite de {$maxLoanPerUser} livros emprestados.");
        }
        
        $aluguel = new Aluguel();
        $aluguel->id_usuario = $request->id_usuario;
        $aluguel->id_livro = $request->id_livro;
        $aluguel->dt_aluguel = $request->dt_aluguel;
        $aluguel->dt_devolucao = $request->dt_devolucao;
        $aluguel->ds_status = Aluguel::STATUS_ATIVO;
        $aluguel->save();
        
        $livro->quantidade -= 1;
        $livro->save();
        
        return redirect()->route('rentals.index')->with('success', 'Aluguel registrado com sucesso!');
    }

    public function return($id)
    {
        $aluguel = Aluguel::findOrFail($id);
        
        if ($aluguel->ds_status == Aluguel::STATUS_DEVOLVIDO) {
            return redirect()->back()->with('error', 'Este livro já foi devolvido.');
        }
        
        $aluguel->ds_status = Aluguel::STATUS_DEVOLVIDO;
        $aluguel->dt_devolucao_efetiva = Carbon::now()->format('Y-m-d');
        $aluguel->save();
        
        $livro = Livro::findOrFail($aluguel->id_livro);
        $livro->quantidade += 1;
        $livro->save();
        
        return redirect()->back()->with('success', 'Livro devolvido com sucesso!');
    }

    public function history(Request $request)
    {
        $settings = Settings::getAllSettings();
        
        $query = Aluguel::with(['usuario', 'livro'])
            ->where('ds_status', Aluguel::STATUS_DEVOLVIDO);
        
        if ($request->filled('user')) {
            $query->whereHas('usuario', function($q) use ($request) {
                $q->where('nome', 'LIKE', '%' . $request->user . '%');
            });
        }
        
        if ($request->filled('book')) {
            $query->whereHas('livro', function($q) use ($request) {
                $q->where('titulo', 'LIKE', '%' . $request->book . '%');
            });
        }
        
        if ($request->filled('start_date')) {
            $query->where('dt_aluguel', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('dt_aluguel', '<=', $request->end_date);
        }
        
        $alugueis = $query->orderBy('id_aluguel', 'desc')
            ->paginate($settings['items_per_page'] ?? 20)
            ->appends($request->query());
            
        return view('rentals.history', compact('alugueis'));
    }

    public function renew($id)
    {
        $aluguel = Aluguel::findOrFail($id);
        
        if (!$aluguel->podeRenovar()) {
            return redirect()->back()->with('error', 'Este aluguel não pode ser renovado.');
        }
        
        $settings = Settings::getAllSettings();
        $periodoRenovacao = $settings['renewal_period'] ?? 14;
        
        $novaDtDevolucao = Carbon::parse($aluguel->dt_devolucao)->addDays($periodoRenovacao);
        
        DB::beginTransaction();
        
        try {
            $renovacao = new Renovacao();
            $renovacao->id_aluguel = $aluguel->id_aluguel;
            $renovacao->dt_renovacao = Carbon::now();
            $renovacao->dt_devolucao_nova = $novaDtDevolucao;
            $renovacao->ds_status = 'Concluído';
            $renovacao->save();
            
            $aluguel->dt_devolucao = $novaDtDevolucao;
            $aluguel->ds_status = Aluguel::STATUS_ATIVO;
            $aluguel->nu_renovacoes = $aluguel->nu_renovacoes + 1;
            $aluguel->save();
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Aluguel renovado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao renovar aluguel: ' . $e->getMessage());
        }
    }

    public function searchUsers(Request $request)
    {
        $term = $request->query('term');
        
        $usuarios = Usuario::where('nome', 'LIKE', "%{$term}%")
            ->orWhere('email', 'LIKE', "%{$term}%")
            ->limit(10)
            ->get();
            
        return response()->json($usuarios);
    }

    public function searchBooks(Request $request)
    {
        try {
            if ($request->has('id')) {
                $livros = Livro::where('id_livro', $request->id)
                    ->where('quantidade', '>', 0)
                    ->get();
                
                return response()->json($livros);
            }
            
            $term = $request->query('term', '');
            
            $columns = ['id_livro', 'titulo', 'autor', 'editor', 'ano_publicacao', 'quantidade'];
            
            if (empty($term)) {
                $livros = Livro::select($columns)
                    ->where('quantidade', '>', 0)
                    ->orderBy('titulo')
                    ->limit(20)
                    ->get();
            } else {
                $livros = Livro::select($columns)
                    ->where(function($query) use ($term) {
                        $query->where('titulo', 'LIKE', "%{$term}%")
                              ->orWhere('autor', 'LIKE', "%{$term}%");
                    })
                    ->where('quantidade', '>', 0)
                    ->limit(10)
                    ->get();
            }
            
            foreach ($livros as $livro) {
                $livro->has_capa = $livro->getOriginal('capa') ? true : false;
            }
                
            return response()->json($livros);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar livros: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'error' => true,
                'message' => 'Erro ao buscar livros: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $aluguel = Aluguel::with(['usuario', 'livro', 'renovacoes'])->findOrFail($id);
        
        $settings = Settings::getAllSettings();

        if (!$aluguel->isAtrasado()) {
            return view('rentals.details', compact('aluguel', 'settings'));
        }
        
        $diasAtraso = $aluguel->diasAtraso();
        
        return view('rentals.details', compact('aluguel', 'diasAtraso', 'settings'));
    }

    public function sendNotification($id)
    {
        try {
            $aluguel = Aluguel::with(['usuario', 'livro'])->findOrFail($id);
            $settings = Settings::getAllSettings();
            
            if (!isset($settings['enable_email_notifications']) || !$settings['enable_email_notifications']) {
                return redirect()->back()->with('error', 'As notificações por e-mail estão desativadas nas configurações do sistema.');
            }
            
            if ($aluguel->ds_status === Aluguel::STATUS_DEVOLVIDO) {
                return redirect()->back()->with('error', 'Não é possível enviar notificação para um aluguel já devolvido.');
            } else if ($aluguel->ds_status !== Aluguel::STATUS_ATRASADO) {
                return redirect()->back()->with('error', 'Apenas aluguéis atrasados podem receber notificações.');
            }
            
            if (!$aluguel->usuario || empty($aluguel->usuario->email)) {
                return redirect()->back()->with('error', 'Usuário não possui e-mail cadastrado.');
            }
            
            \Log::info('Enviando notificação para ' . $aluguel->usuario->email . ' sobre o livro ' . $aluguel->livro->titulo);
            
            $success = $aluguel->enviarNotificacaoAtraso();
            
            if ($success) {
                return redirect()->back()->with('success', 'Notificação de atraso enviada com sucesso!');
            } else {
                return redirect()->back()->with('error', 'Houve um problema ao enviar a notificação. Verifique a configuração de e-mail.');
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar notificação: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Erro ao enviar notificação: ' . $e->getMessage());
        }
    }
}