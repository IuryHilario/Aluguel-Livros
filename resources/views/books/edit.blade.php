@extends('layouts.app')

@section('title', 'Editar Livro - ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Editar Livro')

@section('breadcrumb')
    <a href="{{ route('books.index') }}">Livros</a> / <span>Editar</span>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3>Editar: {{ $livro->titulo }}</h3>
    </div>
    <div class="panel-body">
        <form action="{{ route('books.update', $livro->id_livro) }}" method="POST" enctype="multipart/form-data" class="book-form">
            @csrf
            @method('PUT')

            <x-form.input
                label="Título do Livro"
                placeholder="Digite o título do livro"
                type="text"
                name="titulo"
                id="titulo"
                value="{{ old('titulo', $livro->titulo) }}"
                required
            />

            <x-form.input
                label="Autor"
                placeholder="Digite o nome do autor"
                type="text"
                name="autor"
                id="autor"
                value="{{ old('autor', $livro->autor) }}"
                required
            />

            <x-form.input
                label="Editor"
                placeholder="Digite o nome da editora"
                type="text"
                name="editor"
                id="editor"
                value="{{ old('editor', $livro->editor) }}"
                required
            />

            <x-form.input
                label="Ano Publicação"
                placeholder="Digite o ano de publicação"
                type="number"
                name="ano_publicacao"
                id="ano_publicacao"
                value="{{ old('ano_publicacao', $livro->ano_publicacao) }}"
                min="1000"
                max="{{ date('Y') }}"
                required
            />

            <x-form.input
                label="Quantidade de Exemplares"
                placeholder="Digite a quantidade de exemplares"
                type="number"
                name="quantidade"
                id="quantidade"
                value="{{ old('quantidade', $livro->quantidade) }}"
                min="0"
                required
            />
            
            <div class="form-group">
                <label for="capa">Capa do Livro</label>
                <div class="current-cover mb-2">
                    @if($livro->capa)
                        <div class="d-flex align-items-center">
                            <img src="{{ route('books.capa', $livro->id_livro) }}" alt="Capa atual" class="img-thumbnail" style="max-height: 100px;">
                            <span class="ml-2">Capa atual</span>
                        </div>
                    @else
                        <p>Nenhuma capa disponível</p>
                    @endif
                </div>
                <div class="custom-file">
                    <input type="file" name="capa" id="capa" class="custom-file-input @error('capa') is-invalid @enderror">
                    <label class="custom-file-label" for="capa">Escolher nova capa...</label>
                    @error('capa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <small class="form-text text-muted">Formatos aceitos: JPG, PNG. Tamanho máximo: 2MB</small>
                <div id="image-preview" class="mt-2 d-none">
                    <img src="" alt="Preview da nova capa" class="img-thumbnail" style="max-height: 200px;">
                </div>
            </div>
            
            <div class="form-actions">
                <a href="{{ route('books.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Atualizar Livro</button>
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
            fileLabel.textContent = 'Escolher nova capa...';
            preview.classList.add('d-none');
        }
    });
</script>
@endsection