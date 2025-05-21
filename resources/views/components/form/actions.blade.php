<div class="flex space-x-2">
    @if(!empty($show))
        <a href="{{ $show }}" class="action-btn view" title="Visualizar">
            <i class="fas fa-eye"></i>
        </a>
    @endif

    @if(!empty($edit))
        <a href="{{ $edit }}" class="action-btn edit" title="Editar">
            <i class="fas fa-edit"></i>
        </a>
    @endif

    @if(!empty($delete))
        <form action="{{ $delete }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este item?');"
            class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="action-btn delete" title="Excluir">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endif

    @if(!empty($devolver))
        <a href="{{ $devolver }}" class="action-btn return" title="Devolver">
            <i class="fas fa-undo"></i>
        </a>
    @endif

    @if(!empty($email))
        <a href="{{ $email }}" class="action-btn email" title="Enviar Email" data-rental-id="{{ substr(strrchr($email, '/'), 1) }}">
            <i class="fas fa-envelope"></i>
        </a>
    @endif
</div>
