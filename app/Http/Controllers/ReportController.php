<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aluguel;
use App\Models\Livro;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $reportData = $this->getSummaryData();

        $monthlyStats = $this->getMonthlyRentalStats();
        
        $topBooks = $this->getTopBooks();
        
        $overdueRentals = $this->getOverdueRentals();

        $activeUsers = $this->getMostActiveUsers();

        return view('reports.index', compact(
            'reportData', 
            'monthlyStats', 
            'topBooks', 
            'overdueRentals',
            'activeUsers'
        ));
    }

    private function getSummaryData()
    {
        $totalBooks = Livro::count();
        $totalCopies = Livro::sum('quantidade');
        $totalUsers = Usuario::count();
        $activeRentals = Aluguel::whereIn('ds_status', [Aluguel::STATUS_ATIVO, Aluguel::STATUS_ATRASADO])->count();
        $overdueRentals = Aluguel::where('ds_status', Aluguel::STATUS_ATRASADO)
            ->orWhere(function($query) {
                $query->where('ds_status', Aluguel::STATUS_ATIVO)
                      ->where('dt_devolucao', '<', now()->format('Y-m-d'));
            })->count();
        
        return [
            'totalBooks' => $totalBooks,
            'totalCopies' => $totalCopies,
            'totalUsers' => $totalUsers,
            'activeRentals' => $activeRentals,
            'overdueRentals' => $overdueRentals,
        ];
    }

    private function getMonthlyRentalStats()
    {
        $stats = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $newRentals = Aluguel::where('dt_aluguel', '>=', $monthStart)
                ->where('dt_aluguel', '<=', $monthEnd)
                ->count();
                
            $returnedRentals = Aluguel::where('ds_status', Aluguel::STATUS_DEVOLVIDO)
                ->where('created_at', '>=', $monthStart)
                ->where('created_at', '<=', $monthEnd)
                ->count();
            
            $overdueCount = Aluguel::where(function($query) use ($monthEnd) {
                    $query->where('ds_status', Aluguel::STATUS_ATRASADO)
                          ->orWhere(function($q) use ($monthEnd) {
                              $q->where('ds_status', Aluguel::STATUS_ATIVO)
                                ->where('dt_devolucao', '<', $monthEnd);
                          });
                })
                ->where('dt_aluguel', '<=', $monthEnd)
                ->count();
            
            $stats[] = [
                'month' => $month->format('M Y'),
                'newRentals' => $newRentals,
                'returnedRentals' => $returnedRentals,
                'overdueCount' => $overdueCount
            ];
        }
        
        return $stats;
    }

    private function getTopBooks()
    {
        return DB::table('livro')
            ->select('livro.*', DB::raw('COUNT(aluguel.id_aluguel) as aluguel_count'))
            ->leftJoin('aluguel', 'livro.id_livro', '=', 'aluguel.id_livro')
            ->groupBy('livro.id_livro', 'livro.titulo', 'livro.autor', 'livro.editor', 
                     'livro.ano_publicacao', 'livro.capa', 'livro.quantidade', 
                     'livro.created_at', 'livro.updated_at')
            ->orderBy('aluguel_count', 'desc')
            ->limit(5)
            ->get();
    }

    private function getOverdueRentals()
    {
        return Aluguel::with(['usuario', 'livro'])
            ->where('ds_status', Aluguel::STATUS_ATRASADO)
            ->orWhere(function($query) {
                $query->where('ds_status', Aluguel::STATUS_ATIVO)
                      ->where('dt_devolucao', '<', Carbon::now()->format('Y-m-d'));
            })
            ->orderBy('dt_devolucao', 'asc')
            ->limit(10)
            ->get();
    }

    private function getMostActiveUsers()
    {
        return DB::table('usuario')
            ->select('usuario.*', DB::raw('COUNT(aluguel.id_aluguel) as aluguel_count'))
            ->leftJoin('aluguel', 'usuario.id_usuario', '=', 'aluguel.id_usuario')
            ->groupBy('usuario.id_usuario', 'usuario.nome', 'usuario.email', 'usuario.telefone',
                      'usuario.created_at', 'usuario.updated_at')
            ->orderBy('aluguel_count', 'desc')
            ->limit(5)
            ->get();
    }

    public function generatePdf(Request $request)
    {
        return redirect()->back()->with('info', 'Geração de PDF será implementada em breve.');
    }

    public function overdueFilter(Request $request)
    {
        $overdueRentalsQuery = Aluguel::with(['usuario', 'livro'])
            ->where(function($query) {
                $query->where('ds_status', Aluguel::STATUS_ATRASADO)
                      ->orWhere(function($q) {
                          $q->where('ds_status', Aluguel::STATUS_ATIVO)
                            ->where('dt_devolucao', '<', Carbon::now()->toDateString());
                      });
            });

        if ($request->filled('start_date') || $request->filled('end_date') || $request->filled('min_days')) {
            if ($request->filled('start_date')) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $overdueRentalsQuery->where('dt_devolucao', '>=', $startDate->toDateString());
            }
            
            if ($request->filled('end_date')) {
                $endDate = Carbon::parse($request->end_date)->endOfDay();
                $overdueRentalsQuery->where('dt_devolucao', '<=', $endDate->toDateString());
            }
            
            if ($request->filled('min_days')) {
                $minDays = (int) $request->min_days;
                $cutoffDate = Carbon::now()->subDays($minDays)->endOfDay();
                $overdueRentalsQuery->where('dt_devolucao', '<=', $cutoffDate->toDateString());
            }
        }

        $overdueRentals = $overdueRentalsQuery->get();

        foreach ($overdueRentals as $rental) {
            $rental->atualizaStatusAtrasado();
        }

        return view('reports.partials.overdue_rentals', compact('overdueRentals'))->render();
    }
}