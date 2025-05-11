@extends('layouts.app')

@section('title', 'Adicionar Livro - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Adicionar Novo Livro')

@vite(['resources/css/books/books.css'])

@section('breadcrumb')
<a href="{{ route('books.index') }}">Livros</a> / <span>Adicionar</span>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3>Formulário de Cadastro</h3>
    </div>
    <div class="panel-body">
        <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data" class="book-form">
            @csrf
            
            <div class="form-group">
                <label for="titulo">Título do Livro *</label>
                <input type="text" name="titulo" id="titulo" class="form-control @error('titulo') is-invalid @enderror" autocomplete="off"
                    value="{{ old('titulo') }}" required>
                @error('titulo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="autor">Autor *</label>
                <input type="text" name="autor" id="autor" class="form-control @error('autor') is-invalid @enderror" autocomplete="off"
                    value="{{ old('autor') }}" required>
                @error('autor')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="editor">Editora</label>
                <input type="text" name="editor" id="editor" class="form-control @error('editor') is-invalid @enderror" autocomplete="off"
                    value="{{ old('editor') }}">
                @error('editor')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="ano_publicacao">Ano de Publicação *</label>
                <input type="number" name="ano_publicacao" id="ano_publicacao" min="1000" max="{{ date('Y') }}"
                    class="form-control @error('ano_publicacao') is-invalid @enderror" 
                    value="{{ old('ano_publicacao', date('Y')) }}" required>
                @error('ano_publicacao')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="quantidade">Quantidade de Exemplares *</label>
                <input type="number" name="quantidade" id="quantidade" min="0"
                    class="form-control @error('quantidade') is-invalid @enderror" 
                    value="{{ old('quantidade', 1) }}" required>
                @error('quantidade')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="capa">Capa do Livro</label>
                <div class="custom-file">
                    <input type="file" name="capa" id="capa" class="custom-file-input @error('capa') is-invalid @enderror">
                    <label class="custom-file-label" for="capa">Escolher arquivo...</label>
                    @error('capa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <small class="form-text text-muted">Formatos aceitos: JPG, PNG. Tamanho máximo: 2MB</small>
                <div id="image-preview" class="mt-2 d-none">
                    <img src="" alt="Preview da capa" class="img-thumbnail" style="max-height: 200px;">
                </div>
            </div>
            
            <div class="form-actions">
                <a href="{{ route('books.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar Livro</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Preview da imagem selecionada
    document.getElementById('capa').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('image-preview');
        const previewImg = preview.querySelector('img');
        const fileLabel = document.querySelector('.custom-file-label');
        
        if (file) {
            fileLabel.textContent = file.name;
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('d-none');
            }
            
            reader.readAsDataURL(file);
        } else {
            fileLabel.textContent = 'Escolher arquivo...';
            preview.classList.add('d-none');
        }
    });
</script>
@endsection