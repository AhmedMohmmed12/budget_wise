@if ($paginator->hasPages())
    <nav class="mt-4 flex items-center justify-between font-mono text-xs text-ink-soft" aria-label="Pages">
        @if ($paginator->onFirstPage())
            <span class="opacity-50">&larr; newer</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="link" rel="prev">&larr; newer</a>
        @endif

        <span>page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="link" rel="next">older &rarr;</a>
        @else
            <span class="opacity-50">older &rarr;</span>
        @endif
    </nav>
@endif
