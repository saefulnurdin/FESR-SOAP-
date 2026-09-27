@extends('layouts.app')

@section('title', 'Jenis Dokumen — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Jenis dokumen</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Katalog jenis dokumen yang dikenali fasilitas kesehatan. Struktur pengisiannya
                    disusun pada template di dalam masing-masing jenis dokumen.
                </p>
            </div>

            <a href="{{ route('admin.document-types.create') }}"
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                Tambah jenis dokumen
            </a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Kode</th>
                        <th scope="col" class="px-4 py-3 font-medium">Nama</th>
                        <th scope="col" class="px-4 py-3 font-medium">Template</th>
                        <th scope="col" class="px-4 py-3 font-medium">Status</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($documentTypes as $documentType)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-400">
                                {{ $documentType->code }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $documentType->name }}</span>
                                @if ($documentType->description)
                                    <span class="block text-xs text-slate-500 dark:text-slate-400">
                                        {{ $documentType->description }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $documentType->templates_count }} template
                            </td>
                            <td class="px-4 py-3">
                                @if ($documentType->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-4">
                                    <a href="{{ route('admin.document-types.templates.index', $documentType) }}"
                                        class="font-medium text-teal-700 hover:underline dark:text-teal-400">
                                        Template
                                    </a>

                                    <a href="{{ route('admin.document-types.edit', $documentType) }}"
                                        class="font-medium text-teal-700 hover:underline dark:text-teal-400">
                                        Ubah
                                    </a>

                                    <form method="POST" action="{{ route('admin.document-types.destroy', $documentType) }}"
                                        x-data
                                        x-on:submit.prevent="if (confirm('Hapus jenis dokumen {{ $documentType->name }}?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')

                                        <x-danger-button class="px-3 py-1.5 text-xs">Hapus</x-danger-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                Belum ada jenis dokumen.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $documentTypes->links() }}
        </div>
    </div>
@endsection
