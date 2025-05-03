<!-- Sidebar Component -->
<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo-circle">
            <i class="fas fa-book"></i>
        </div>
        <h1>{{ $settings['system_name'] ?? 'Aluga Livros' }}</h1>
    </div>
    
    <div class="sidebar-menu">
        <div class="menu-header">MENU PRINCIPAL</div>
        <ul>
            <li class="{{ request()->is('dashboard*') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="{{ request()->is('books*') ? 'active expanded' : '' }}">
                <a href="#"><i class="fas fa-book"></i> Livros</a>
                <ul class="submenu">
                    <li><a href="{{ route('books.index') }}"><i class="fas fa-list"></i> Listar Todos</a></li>
                    <li><a href="{{ route('books.create') }}"><i class="fas fa-plus"></i> Adicionar Novo</a></li>
                    <li><a href="{{ route('books.categories') }}"><i class="fas fa-tags"></i> Categorias</a></li>
                </ul>
            </li>
            <li class="{{ request()->is('rentals*') ? 'active expanded' : '' }}">
                <a href="#"><i class="fas fa-handshake"></i> Aluguéis</a>
                <ul class="submenu">
                    <li><a href="{{ route('rentals.index') }}"><i class="fas fa-list"></i> Listar Todos</a></li>
                    <li><a href="{{ route('rentals.create') }}"><i class="fas fa-plus"></i> Novo Aluguel</a></li>
                    <li><a href="{{ route('rentals.history') }}"><i class="fas fa-history"></i> Histórico</a></li>
                </ul>
            </li>
            <li class="{{ request()->is('users*') ? 'active expanded' : '' }}">
                <a href="#"><i class="fas fa-users"></i> Usuários</a>
                <ul class="submenu">
                    <li><a href="{{ route('users.index') }}"><i class="fas fa-list"></i> Listar Todos</a></li>
                    <li><a href="{{ route('users.create') }}"><i class="fas fa-user-plus"></i> Adicionar Novo</a></li>
                </ul>
            </li>
            <li class="{{ request()->is('reports*') ? 'active' : '' }}">
                <a href="{{ route('reports.index') }}"><i class="fas fa-chart-line"></i> Relatórios</a>
            </li>
        </ul>
        
        <div class="menu-header">CONFIGURAÇÕES</div>
        <ul>
            <li class="{{ request()->is('profile*') ? 'active' : '' }}">
                <a href="{{ route('profile.edit') }}"><i class="fas fa-user-circle"></i> Meu Perfil</a>
            </li>
            <li class="{{ request()->is('settings*') ? 'active' : '' }}">
                <a href="{{ route('settings.index') }}"><i class="fas fa-cog"></i> Configurações</a>
            </li>
            <li>
                <a href="{{ route('logout') }}"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </li>
        </ul>
    </div>
</div>