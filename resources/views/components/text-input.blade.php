@props(['disabled' => false])

@php
    /*
     * Nama field diambil dari atribut "name" agar cincin merah pada input
     * otomatis mengikuti ringkasan galat yang ditampilkan layout.
     *
     * Class dari pemanggil digabung lewat $attributes->class(), bukan
     * ditambahkan sebagai atribut class kedua, karena HTML hanya membaca
     * nilai class pertama sehingga class tambahan seperti "mt-1" hilang.
     */
    $name = $attributes->get('name');
    $hasError = filled($name) && $errors->has($name);
@endphp

<input @disabled($disabled) {{ $attributes->class([
    'block w-full rounded-lg border px-3 py-2 text-sm shadow-xs transition focus:ring-2 focus:outline-hidden',
    'border-rose-400 focus:border-rose-500 focus:ring-rose-500/40 dark:border-rose-500' => $hasError,
    'border-slate-300 bg-white text-slate-800 focus:border-teal-500 focus:ring-teal-500/40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100' => ! $hasError,
]) }}>
