@extends('layouts.app')

@section('title', 'Novo Aluguel - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Novo Aluguel')

@vite(['resources/css/rentals/rentals.css', 'resources/js/rentals/create.js'])

@section('breadcrumb')
<a href="{{ route('rentals.index') }}">Aluguéis</a> / <span>Novo</span>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3>Formulário de Aluguel</h3>
    </div>
    <div class="panel-body">
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        
        <form action="{{ route('rentals.store') }}" method="POST" class="rental-form">
            @csrf
            
            <!-- Selecionar Usuário -->
            <div class="form-group">
                <label for="usuario">Usuário</label>
                <div class="search-container">
                    <input type="text" id="usuario_nome" class="form-control" 
                          placeholder="Clique para buscar usuário" readonly>
                    <input type="hidden" name="id_usuario" id="id_usuario" required>
                    <button type="button" class="search-btn" id="buscarUsuarioBtn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                @error('id_usuario')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Selecionar Livro -->
            <div class="form-group">
                <label for="livro">Livro</label>
                <div class="search-container">
                    <input type="text" id="livro_titulo" class="form-control" 
                          placeholder="Clique para buscar livro" readonly>
                    <input type="hidden" name="id_livro" id="id_livro" required>
                    <button type="button" class="search-btn" id="buscarLivroBtn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                @error('id_livro')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Datas -->
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="dt_aluguel">Data de Retirada</label>
                    <input type="date" name="dt_aluguel" id="dt_aluguel" class="form-control" 
                           value="{{ date('Y-m-d') }}" required>
                    @error('dt_aluguel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="dt_devolucao">Data de Devolução</label>
                    <input type="date" name="dt_devolucao" id="dt_devolucao" class="form-control" 
                           value="{{ date('Y-m-d', strtotime('+' . ($settings['default_loan_period'] ?? 14) . ' days')) }}" required>
                    @error('dt_devolucao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Preview de informações -->
            <div class="preview-info" id="preview-container" style="display: none;">
                <h4>Resumo do Aluguel</h4>
                <div class="preview-container">
                    <div class="preview-line">
                        <div class="preview-label">Usuário:</div>
                        <div class="preview-value" id="preview-usuario"></div>
                    </div>
                    <div class="preview-line">
                        <div class="preview-label">Livro:</div>
                        <div class="preview-value" id="preview-livro"></div>
                    </div>
                    <div class="preview-line">
                        <div class="preview-label">Autor:</div>
                        <div class="preview-value" id="preview-autor"></div>
                    </div>
                    <div class="preview-line">
                        <div class="preview-label">Período:</div>
                        <div class="preview-value" id="preview-periodo"></div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-register">Registrar Aluguel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Buscar Usuário -->
<div class="modal" id="usuarioModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar Usuário</h5>
                <button type="button" class="close" id="fecharModalUsuario">&times;</button>
            </div>
            <div class="modal-body">
                <div class="search-input-container">
                    <input type="text" id="searchUsuario" class="form-control" placeholder="Digite nome ou email do usuário">
                    <button id="btnSearchUsuario" class="btn btn-primary">Buscar</button>
                </div>
                <div id="usuariosSearchResults" class="search-results"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buscar Livro -->
<div class="modal" id="livroModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar Livro</h5>
                <button type="button" class="close" id="fecharModalLivro">&times;</button>
            </div>
            <div class="modal-body">
                <div class="search-input-container">
                    <input type="text" id="searchLivro" class="form-control" placeholder="Digite título ou autor do livro">
                    <button id="btnSearchLivro" class="btn btn-primary">Buscar</button>
                </div>
                <div id="livrosSearchResults" class="search-results"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@endpush
@endsection