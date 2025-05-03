<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use App\Models\Usuario;
use App\Models\Aluguel;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalLivros = Livro::count();
        $totalUsuarios = Usuario::count();
        $totalAlugueis = Aluguel::whereIn('ds_status', [Aluguel::STATUS_ATIVO, Aluguel::STATUS_ATRASADO])->count();
        $totalAtrasos = Aluguel::where('ds_status', Aluguel::STATUS_ATRASADO)
            ->orWhere(function($query) {
                $query->where('ds_status', Aluguel::STATUS_ATIVO)
                      ->where('dt_devolucao', '<', now()->format('Y-m-d'));
            })->count();
        
        $livrosPercentChange = $this->calcularCrescimentoLivros();
        $usuariosPercentChange = $this->calcularCrescimentoUsuarios();
        $alugueisPeriodChange = $this->calcularVariacaoAlugueis();
        $atrasosPeriodChange = $this->calcularVariacaoAtrasos();
        
        $alugueis = Aluguel::with(['livro', 'usuario'])
            ->orderBy('id_aluguel', 'desc')
            ->limit(5)
            ->get();
            
        $livrosPopulares = Livro::getLivrosPopulares(4);
        
        return view('dashboard.dashboard', compact(
            'totalLivros', 'totalUsuarios', 'totalAlugueis', 'totalAtrasos',
            'livrosPercentChange', 'usuariosPercentChange', 
            'alugueisPeriodChange', 'atrasosPeriodChange',
            'alugueis', 'livrosPopulares'
        ));
    }
    
    private function calcularCrescimentoUsuarios()
    {
        $hoje = Carbon::now();
        
        $inicioMesAtual = Carbon::now()->startOfMonth();
        
        $inicioMesAnterior = Carbon::now()->subMonth()->startOfMonth();
        
        $fimMesAnterior = Carbon::now()->subMonth()->endOfMonth();
        
        $usuariosMesAtual = Usuario::where('created_at', '>=', $inicioMesAtual)
                                    ->count();
        
        $usuariosMesAnterior = Usuario::where('created_at', '>=', $inicioMesAnterior)
                                      ->where('created_at', '<=', $fimMesAnterior)
                                      ->count();
        

        if ($usuariosMesAnterior > 0) {
            $percentChange = (($usuariosMesAtual - $usuariosMesAnterior) / $usuariosMesAnterior) * 100;
        } else {
            $percentChange = $usuariosMesAtual > 0 ? 100 : 0;
        }
        
        return round($percentChange, 2);
    }
    
    private function calcularCrescimentoLivros()
    {
        $hoje = Carbon::now();
        
        $inicioMesAtual = Carbon::now()->startOfMonth();
        
        $inicioMesAnterior = Carbon::now()->subMonth()->startOfMonth();
        
        $fimMesAnterior = Carbon::now()->subMonth()->endOfMonth();
        
        $livrosMesAtual = Livro::where('created_at', '>=', $inicioMesAtual)
                                ->count();
        
        $livrosMesAnterior = Livro::where('created_at', '>=', $inicioMesAnterior)
                                  ->where('created_at', '<=', $fimMesAnterior)
                                  ->count();
        
        if ($livrosMesAnterior > 0) {
            $percentChange = (($livrosMesAtual - $livrosMesAnterior) / $livrosMesAnterior) * 100;
        } else {
            $percentChange = $livrosMesAtual > 0 ? 100 : 0;
        }
        
        return round($percentChange, 2);
    }

    private function calcularVariacaoAlugueis()
    {
        $inicioMesAtual = Carbon::now()->startOfMonth();
        
        $inicioMesAnterior = Carbon::now()->subMonth()->startOfMonth();
        
        $fimMesAnterior = Carbon::now()->subMonth()->endOfMonth();
        
        $alugueisMesAtual = Aluguel::where('created_at', '>=', $inicioMesAtual)->count();
        
        $alugueisMesAnterior = Aluguel::where('created_at', '>=', $inicioMesAnterior)
                                    ->where('created_at', '<=', $fimMesAnterior)
                                    ->count();
        
        if ($alugueisMesAnterior > 0) {
            $percentChange = (($alugueisMesAtual - $alugueisMesAnterior) / $alugueisMesAnterior) * 100;
        } else {
            $percentChange = $alugueisMesAtual > 0 ? 100 : 0;
        }
        
        return round($percentChange, 2);
    }
    
    private function calcularVariacaoAtrasos()
    {
        $inicioMesAtual = Carbon::now()->startOfMonth();
        
        $inicioMesAnterior = Carbon::now()->subMonth()->startOfMonth();
        
        $fimMesAnterior = Carbon::now()->subMonth()->endOfMonth();
        
        $atrasosMesAtual = Aluguel::where(function($query) use ($inicioMesAtual) {
                                $query->where('created_at', '>=', $inicioMesAtual)
                                      ->where(function($q) {
                                          $q->where('ds_status', Aluguel::STATUS_ATRASADO)
                                            ->orWhere(function($inner) {
                                                $inner->where('ds_status', Aluguel::STATUS_ATIVO)
                                                      ->where('dt_devolucao', '<', Carbon::now()->toDateString());
                                            });
                                      });
                            })->count();
        
        $atrasosMesAnterior = Aluguel::where(function($query) use ($inicioMesAnterior, $fimMesAnterior) {
                                  $query->where('created_at', '>=', $inicioMesAnterior)
                                        ->where('created_at', '<=', $fimMesAnterior)
                                        ->where(function($q) use ($fimMesAnterior) {
                                            $q->where('ds_status', Aluguel::STATUS_ATRASADO)
                                              ->orWhere(function($inner) use ($fimMesAnterior) {
                                                  $inner->where('ds_status', Aluguel::STATUS_ATIVO)
                                                        ->where('dt_devolucao', '<', $fimMesAnterior->toDateString());
                                              });
                                        });
                              })->count();
        
        if ($atrasosMesAnterior > 0) {
            $percentChange = (($atrasosMesAtual - $atrasosMesAnterior) / $atrasosMesAnterior) * 100;
        } else {
            $percentChange = $atrasosMesAtual > 0 ? 100 : 0;
        }
        
        return round($percentChange, 2);
    }
}