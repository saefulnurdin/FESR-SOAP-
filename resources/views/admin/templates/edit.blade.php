@extends('layouts.app')

@section('title', 'Struktur ' . $template->name . ' — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-4xl">
        <nav class="text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('admin.document-types.index') }}" class="hover:underline">Jenis dokumen</a>
            <span class="mx-1">/</span>
            <a href="{{ route('admin.document-types.templates.index', $template->documentType) }}"
                class="hover:underline">
                {{ $template->documentType->name }}
            </a>
            <span class="mx-1">/</span>
            <span class="text-slate-700 dark:text-slate-200">{{ $template->name }}</span>
        </nav>

        <h1 class="mt-4 text-2xl font-bold tracking-tight">{{ $template->name }}</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Key bagian dan key isian menjadi nama kolom saat hasil rekaman dipetakan ke dokumen.
            Menjaga keduanya tetap stabil membuat rekaman lama tetap dapat dibaca.
        </p>

        <section class="mt-8">
            <h2 class="text-base font-semibold">Pengaturan template</h2>

            <div class="mt-3 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                @include('admin.templates.partials.form')
            </div>
        </section>

        <section class="mt-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-base font-semibold">
                    Struktur
                    <span class="font-normal text-slate-500 dark:text-slate-400">
                        ({{ $template->sections->count() }} bagian,
                        {{ $template->fields->count() }} isian)
                    </span>
                </h2>
            </div>

            @forelse ($template->sections as $section)
                <article class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <header
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/40">
                        <div>
                            <h3 class="font-medium">{{ $section->title }}</h3>
                            <p class="font-mono text-xs text-slate-500 dark:text-slate-400">
                                {{ $section->key }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <a href="{{ route('admin.templates.fields.create', [$template, 'section' => $section->id]) }}"
                                class="text-sm font-medium text-teal-700 hover:underline dark:text-teal-400">
                                Tambah isian
                            </a>

                            <form method="POST"
                                action="{{ route('admin.templates.sections.destroy', [$template, $section]) }}"
                                x-data
                                x-on:submit.prevent="if (confirm('Hapus bagian {{ $section->title }} beserta seluruh isiannya?')) $el.submit()">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="text-sm font-medium text-rose-700 hover:underline dark:text-rose-400">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </header>

                    @if ($section->hint)
                        <p class="border-b border-slate-200 px-4 py-2 text-sm text-slate-600 dark:border-slate-800 dark:text-slate-400">
                            {{ $section->hint }}
                        </p>
                    @endif

                    @if ($section->fields->isEmpty())
                        <p class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                            Belum ada isian pada bagian ini.
                        </p>
                    @else
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
                            <thead class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                <tr>
                                    <th scope="col" class="px-4 py-2 font-medium">Isian</th>
                                    <th scope="col" class="px-4 py-2 font-medium">Key</th>
                                    <th scope="col" class="px-4 py-2 font-medium">Tipe</th>
                                    <th scope="col" class="px-4 py-2 text-right font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @foreach ($section->fields as $field)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <span class="font-medium">{{ $field->labelWithUnit() }}</span>
                                            @if ($field->is_required)
                                                <span class="ml-1 rounded-full bg-teal-100 px-2 py-0.5 text-xs font-medium text-teal-800 dark:bg-teal-950 dark:text-teal-200">
                                                    Wajib
                                                </span>
                                            @endif
                                            @if ($field->options)
                                                <span class="block text-xs text-slate-500 dark:text-slate-400">
                                                    {{ implode(', ', $field->options) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 font-mono text-xs text-slate-600 dark:text-slate-400">
                                            {{ $field->key }}
                                        </td>
                                        <td class="px-4 py-2 text-slate-600 dark:text-slate-400">
                                            {{ $field->type->label() }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="flex items-center justify-end gap-4">
                                                <a href="{{ route('admin.templates.fields.edit', [$template, $field]) }}"
                                                    class="font-medium text-teal-700 hover:underline dark:text-teal-400">
                                                    Ubah
                                                </a>

                                                <form method="POST"
                                                    action="{{ route('admin.templates.fields.destroy', [$template, $field]) }}"
                                                    x-data
                                                    x-on:submit.prevent="if (confirm('Hapus isian {{ $field->label }}?')) $el.submit()">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                        class="text-sm font-medium text-rose-700 hover:underline dark:text-rose-400">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </article>
            @empty
                <div
                    class="mt-3 rounded-xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Template ini belum memiliki bagian, sehingga belum dapat dipakai untuk
                        membuat dokumen.
                    </p>
                </div>
            @endforelse

            <div class="mt-6">
                @include('admin.templates.partials.section-form')
            </div>
        </section>
    </div>
@endsection
