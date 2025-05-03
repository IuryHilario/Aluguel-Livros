@extends('layouts.app')

@section('title', 'Editoras ' . ($settings['system_name'] ?? 'Aluga Livros'))

@section('page-title', 'Editoras')

@vite(['resources/css/books/books.css'])

@section('breadcrumb')
<a href="{{ route('books.index') }}">Livros</a> / <span>Editoras</span>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <h3>Gerenciar por Editoras</h3>
        <div class="panel-actions">
            <a href="{{ route('books.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Adicionar Livro
            </a>
        </div>
    </div>
    <div class="panel-body">
        @if($editores->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h4>Nenhuma editora encontrada</h4>
                <p>Adicione editoras aos livros para começar a categorizá-los.</p>
                <a href="{{ route('books.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar Livro
                </a>
            </div>
        @else
            <div class="category-list">
                @foreach($editores as $editor)
                <div class="category-card">
                    <div class="category-header">
                        <h4><i class="fas fa-building"></i> {{ $editor }}</h4>
                        @php
                            $booksCount = \App\Models\Livro::where('editor', $editor)->count();
                        @endphp
                        <span class="category-count">{{ $booksCount }} {{ $booksCount == 1 ? 'livro' : 'livros' }}</span>
                    </div>
                    <div class="category-body">
                        <div class="form-actions">
                            <a href="{{ route('books.index') }}?editor={{ urlencode($editor) }}" class="btn btn-primary">
                                <i class="fas fa-book"></i> Ver livros
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            @if(count($editores) > (isset($settings['items_per_page']) ? $settings['items_per_page'] : 10) && method_exists($editores, 'links'))
                <div class="pagination-container">
                    {{ $editores->links('components.pagination') }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection