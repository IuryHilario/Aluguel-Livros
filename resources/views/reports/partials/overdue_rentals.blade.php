@if(count($overdueRentals) > 0)
    <div class="overdue-rentals">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Livro</th>
                    <th>Data Devolução</th>
                    <th>Dias em Atraso</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($overdueRentals as $rental)
                    <tr class="overdue-row">
                        <td>{{ $rental->id_aluguel }}</td>
                        <td>{{ $rental->usuario->nome }}</td>
                        <td>{{ $rental->livro->titulo }}</td>
                        <td>{{ \Carbon\Carbon::parse($rental->dt_devolucao)->format('d/m/Y') }}</td>
                        <td>{{ $rental->diasAtraso() }} dias</td>
                        <td class="actions">
                            <a href="{{ route('rentals.show', $rental->id_aluguel) }}" class="action-btn details" title="Ver Detalhes">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="action-btn notify" data-id="{{ $rental->id_usuario }}" title="Notificar Usuário">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty-state">
        <i class="fas fa-check-circle"></i>
        <p>Não há aluguéis em atraso no momento.</p>
    </div>
@endif