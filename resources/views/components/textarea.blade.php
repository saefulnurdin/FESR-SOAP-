@props(['rows' => 2, 'disabled' => false, 'value' => null])

@php
    /*
     * Isian dikirim lewat prop "value", bukan lewat slot, supaya baris baru
     * di dalam tag tidak ikut menjadi isi textarea.
     */
    $name = $attributes->get('name');
    $hasError = filled($name) && $errors->has($name);
@endphp

<textarea @disabled($disabled) {{ $attributes->class([
    'block w-full rounded-lg border px-3 py-2 text-sm shadow-xs transition focus:ring-2 focus:outline-hidden',
    'border-rose-400 focus:border-rose-500 focus:ring-rose-500/40 dark:border-rose-500' => $hasError,
    'border-slate-300 bg-white text-slate-800 focus:border-teal-500 focus:ring-teal-500/40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100' => ! $hasError,
]) }} rows="{{ $rows }}">{{ $value }}</textarea>
