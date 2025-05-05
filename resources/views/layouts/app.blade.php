<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Aluga Livros')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/dashboard/dashboard.css'])
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
                    <div class="search-box">
                        <input type="text" placeholder="Pesquisar...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                <div class="nav-right">
                    <div class="nav-item">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="nav-item">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">5</span>
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
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>
</html>