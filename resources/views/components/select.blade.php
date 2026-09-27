@props(['options' => [], 'selected' => null, 'disabled' => false])

@php
    /*
     * Sama seperti komponen text-input, nama field diambil dari atribut "name"
     * agar cincin merah pada select otomatis mengikuti ringkasan galat.
     */
    $name = $attributes->get('name');
    $hasError = filled($name) && $errors->has($name);
@endphp

<select @disabled($disabled) {{ $attributes->class([
    'block w-full rounded-lg border px-3 py-2 text-sm shadow-xs transition focus:ring-2 focus:outline-hidden',
    'border-rose-400 focus:border-rose-500 focus:ring-rose-500/40 dark:border-rose-500' => $hasError,
    'border-slate-300 bg-white text-slate-800 focus:border-teal-500 focus:ring-teal-500/40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100' => ! $hasError,
]) }}>
    @foreach ($options as $value => $label)
        <option value="{{ $value }}" @selected((string) $value === (string) $selected)>{{ $label }}</option>
    @endforeach
</select>
