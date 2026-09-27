@props(['type' => 'info'])

@php
    $styles = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100',
        'error' => 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-100',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100',
        'info' => 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-100',
    ];

    $style = $styles[$type] ?? $styles['info'];
@endphp

<div x-data="{ show: true }" x-show="show" x-cloak
    {{ $attributes->merge(['class' => 'mb-4 flex items-start justify-between gap-4 rounded-lg border px-4 py-3 text-sm '.$style]) }}
    role="alert">
    <div class="min-w-0 flex-1">
        {{ $slot }}
    </div>

    <button type="button" x-on:click="show = false" aria-label="Tutup"
        class="shrink-0 cursor-pointer opacity-60 hover:opacity-100">
        &times;
    </button>
</div>
