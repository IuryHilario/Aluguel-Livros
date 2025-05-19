@extends('layouts.app')

@section('title', 'Adicionar Livro - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Adicionar Novo Livro')

@section('breadcrumb')
    <a href="{{ route('books.index') }}">Livros</a> / <span>Adicionar</span>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3>Formulário de Cadastro</h3>
    </div>
    <div class="panel-body">
        <form action="{{ route('books.store') }}" method="POST" class="book-form">
            @csrf
            <x-form.input
                label="Título Livro"
                placeholder="Digite o título do livro"
                type="text"
                name="titulo"
                id="titulo"
                value="{{ old('titulo') }}"
                required
            />

            <x-form.input
                label="Autor"
                placeholder="Digite o nome do autor"
                type="text"
                name="autor"
                id="autor"
                value="{{ old('autor') }}"
                required
            />

            <x-form.input
                label="Editor"
                placeholder="Digite o nome da editor"
                type="text"
                name="editor"
                id="editor"
                value="{{ old('editora') }}"
                required
            />

            <x-form.input
                label="Ano Publicação"
                placeholder="Digite o ano de publicação"
                type="number"
                name="ano_publicacao"
                id="ano_publicacao"
                value="{{ old('ano_publicacao', date('Y')) }}"
                min="1000"
                max="{{ date('Y') }}"
                required
            />

            <x-form.input
                label="Quantidade"
                placeholder="Digite a quantidade de exemplares"
                type="number"
                name="quantidade"
                id="quantidade"
                value="{{ old('quantidade', 1) }}"
                min="0"
                required
            />

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
