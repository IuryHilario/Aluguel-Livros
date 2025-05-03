@if ($paginator->hasPages())
    <style>
        .pagination-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 5px;
        }

        .pagination li {
            display: inline-block;
        }

        .pagination li a,
        .pagination li span {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            min-width: 38px;
            padding: 0 12px;
            border-radius: 6px;
            background-color: #fff;
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }

        .pagination li a:hover {
            background-color: var(--gray-100);
            color: var(--gray-800);
        }

        .pagination li.active span {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }

        .pagination li.disabled span {
            color: var(--gray-500);
            pointer-events: none;
            background-color: var(--gray-100);
        }
    </style>
    <nav>
        <ul class="pagination">
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="disabled" aria-disabled="true"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @php
                        $currentPage = $paginator->currentPage();
                        $lastPage = $paginator->lastPage();
                        $startPage = max(1, min($currentPage - 2, $lastPage - 4));
                        $endPage = min($lastPage, max(5, $currentPage + 2));
                        
                        if ($endPage - $startPage < 4) {
                            $startPage = max(1, $endPage - 4);
                        }
                    @endphp

                    @if ($startPage > 1)
                        <li><a href="{{ $paginator->url(1) }}">1</a></li>
                        @if ($startPage > 2)
                            <li class="disabled" aria-disabled="true"><span>...</span></li>
                        @endif
                    @endif

                    @foreach ($element as $page => $url)
                        @if ($page >= $startPage && $page <= $endPage)
                            @if ($page == $currentPage)
                                <li class="active" aria-current="page"><span>{{ $page }}</span></li>
                            @else
                                <li><a href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endif
                    @endforeach

                    @if ($endPage < $lastPage)
                        @if ($endPage < $lastPage - 1)
                            <li class="disabled" aria-disabled="true"><span>...</span></li>
                        @endif
                        <li><a href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a></li>
                    @endif
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif