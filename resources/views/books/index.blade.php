@extends('layouts.app')

@section('title', 'Livros - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Gerenciar Livros')

@vite(['resources/css/books/books.css'])

@section('breadcrumb')
<a href="{{ route('books.index') }}">Livros</a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3>Livros Disponíveis</h3>
        <div class="panel-actions">
            <button class="btn-icon" id="toggleFilter"><i class="fas fa-filter"></i></button>
            <a href="{{ route('books.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Adicionar Livro
            </a>
        </div>
    </div>
    
    <div class="filter-container" style="display: {{ request()->hasAny(['titulo', 'autor', 'editor', 'ano_publicacao']) ? 'block' : 'none' }};">
        <form action="{{ route('books.index') }}" method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-column">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Título do livro" value="{{ request('titulo') }}" autocomplete="off">
                </div>
                <div class="filter-column">
                    <label for="autor">Autor</label>
                    <input type="text" id="autor" name="autor" class="form-control" placeholder="Nome do autor" value="{{ request('autor') }}" autocomplete="off">
                </div>
                <div class="filter-column">
                    <label for="editor">Editora</label>
                    <input type="text" id="editor" name="editor" class="form-control" placeholder="Nome da editora" value="{{ request('editor') }}" autocomplete="off">
                </div>
                <div class="filter-column">
                    <label for="ano_publicacao">Ano de Publicação</label>
                    <input type="number" id="ano_publicacao" name="ano_publicacao" class="form-control" placeholder="Ano" value="{{ request('ano_publicacao') }}" autocomplete="off">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('books.index') }}" class="btn btn-secondary">Limpar</a>
                </div>
            </div>
        </form>
    </div>
    
    <div class="panel-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(count($livros) > 0)
            <div class="book-grid">
                @foreach($livros as $livro)
                <div class="book-card">
                    <div class="book-cover">
                        @if($livro->capa && $settings['show_book_covers'])
                                <img src="{{ route('books.capa', $livro->id_livro) }}" alt="Capa do livro {{ $livro->titulo }}">
                        @else
                            <div class="no-cover">
                                <i class="fas fa-book"></i>
                            </div>
                        @endif
                    </div>
                    <div class="book-info">
                        <h4 class="book-title">{{ $livro->titulo }}</h4>
                        <p class="book-author">{{ $livro->autor }}</p>
                        @if($livro->editor)
                            <p class="book-editor">{{ $livro->editor }}</p>
                        @endif
                        <p class="book-year">{{ $livro->ano_publicacao }}</p>
                        <div class="book-availability">
                            @if($livro->quantidade_disponivel > 0)
                                <span class="available">Disponível ({{ $livro->quantidade_disponivel }})</span>
                            @else
                                <span class="unavailable">Indisponível</span>
                            @endif
                        </div>
                    </div>
                    <div class="book-actions">
                        <a href="{{ route('books.show', $livro->id_livro) }}" class="btn btn-sm btn-info" title="Ver detalhes">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('books.edit', $livro->id_livro) }}" class="btn btn-sm btn-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('books.destroy', $livro->id_livro) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este livro?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            
            @if(method_exists($livros, 'links'))
                <div class="pagination-container">
                    {{ $livros->appends(request()->query())->links('components.pagination') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-books"></i>
                </div>
                <h4>Nenhum livro encontrado</h4>
                <p>Não foram encontrados livros com os critérios especificados.</p>
                <a href="{{ route('books.index') }}" class="btn btn-secondary">
                    <i class="fas fa-undo"></i> Limpar Filtros
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleFilter = document.getElementById('toggleFilter');
        const filterContainer = document.querySelector('.filter-container');
        
        toggleFilter.addEventListener('click', function() {
            filterContainer.style.display = filterContainer.style.display === 'none' ? 'block' : 'none';
        });
    });
</script>
@endpush
@endsection