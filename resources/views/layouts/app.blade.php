<?php
use App\Models\Aluguel;
use Carbon\Carbon;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Aluga Livros')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ asset('icone-book.ico') }}?v=2" type="image/x-icon">
    @vite(['resources/css/dashboard/dashboard.css'])

    @vite(['resources/css/global/search.css', 'resources/css/components/form.css'])

    @stack('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css">
</head>
<body>
    <div class="dashboard-container">
        @include('components.sidebar')

        <div class="main-content">
            <nav class="navbar">
                <div class="nav-left">
                    <button id="sidebar-toggle" class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="search-container">
                        <div class="search-box">
                            <input type="text" id="global-search" placeholder="Pesquisar livros/usuários..." autocomplete="off">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="search-results-dropdown" id="search-results-dropdown">
                            <div class="search-results-container">
                                <div id="search-results-content"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="nav-right">
                    <div class="nav-item notification-dropdown">
                        <i class="fas fa-bell notification-icon"></i>
                        @php
                            $overdueRentals = Aluguel::with(['usuario', 'livro'])
                                ->where('ds_status', Aluguel::STATUS_ATRASADO)
                                ->orWhere(function($query) {
                                    $query->where('ds_status', Aluguel::STATUS_ATIVO)
                                          ->where('dt_devolucao', '<', Carbon::now()->format('Y-m-d'));
                                })
                                ->orderBy('dt_devolucao', 'DESC')
                                ->limit(5)
                                ->get();
                        @endphp
                        <div class="notification-menu">
                            <div class="notification-header">
                                <h3>Notificações de Atraso</h3>
                            </div>
                            <div class="notification-list">
                                @if(count($overdueRentals) > 0)
                                    @foreach($overdueRentals as $rental)
                                        <a href="{{ route('rentals.show', $rental->id_aluguel) }}" class="notification-item">
                                            <div class="notification-icon">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </div>
                                            <div class="notification-content">
                                                <div class="notification-title">Aluguel de {{ $rental->usuario->nome }} está atrasado</div>
                                                <div class="notification-desc">Livro: {{ $rental->livro->titulo }}</div>
                                                <div class="notification-time">{{ $rental->diasAtraso() }} dias de atraso</div>
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    <div class="empty-notifications">
                                        <i class="fas fa-check-circle"></i>
                                        <p>Não há aluguéis em atraso</p>
                                    </div>
                                @endif
                            </div>
                            @if(count($overdueRentals) > 0)
                                <div class="notification-footer">
                                    <a href="{{ route('rentals.index') }}?status=Atrasado">Ver todos os atrasos</a>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="nav-user">
                        <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'User' }}&background=7b5d48&color=fff" alt="User">
                        <span>{{ Auth::user()->name ?? 'User' }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </nav>

            <div class="content">
                <div class="page-header">
                    <h2>@yield('page-title', 'Dashboard')</h2>
                    <nav class="breadcrumb">
                        <a href="{{ route('dashboard') }}">Home</a> /
                        @yield('breadcrumb')
                    </nav>
                </div>

                @yield('content')
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const dashboardContainer = document.querySelector('.dashboard-container');

            sidebarToggle.addEventListener('click', function() {
                dashboardContainer.classList.toggle('sidebar-collapsed');
            });

            const menuItems = document.querySelectorAll('.sidebar-menu li');
            menuItems.forEach(item => {
                if (item.querySelector('.submenu')) {
                    item.addEventListener('click', function(e) {
                        if (e.target === item.querySelector('a') || e.target === item.querySelector('i')) {
                            e.preventDefault();
                            this.classList.toggle('expanded');
                        }
                    });
                }
            });

            const notificationDropdown = document.querySelector('.notification-dropdown');
            const notificationIcon = notificationDropdown.querySelector('.fa-bell');

            notificationIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('show');
            });

            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });

            document.addEventListener('click', function(e) {
                if (!notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.remove('show');
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/js/global/search.js'])
    @stack('scripts')
</body>
</html>
