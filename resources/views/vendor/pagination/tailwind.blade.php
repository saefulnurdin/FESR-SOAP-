@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center justify-between gap-3 text-sm">
        <p class="text-slate-600 dark:text-slate-400">
            Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="inline-flex size-9 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-slate-400 dark:border-slate-800" aria-disabled="true">
                    <span class="sr-only">Sebelumnya</span>
                    &lsaquo;
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-300 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    <span class="sr-only">Sebelumnya</span>
                    &lsaquo;
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-400 dark:border-slate-800">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                class="inline-flex size-9 items-center justify-center rounded-lg bg-teal-600 font-medium text-white">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                                class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-300 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-300 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    <span class="sr-only">Berikutnya</span>
                    &rsaquo;
                </a>
            @else
                <span class="inline-flex size-9 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-slate-400 dark:border-slate-800" aria-disabled="true">
                    <span class="sr-only">Berikutnya</span>
                    &rsaquo;
                </span>
            @endif
        </div>
    </nav>
@endif
