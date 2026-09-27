@extends('layouts.app')

@section('title', 'Template ' . $documentType->name . ' — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-5xl">
        <nav class="text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('admin.document-types.index') }}" class="hover:underline">Jenis dokumen</a>
            <span class="mx-1">/</span>
            <span class="text-slate-700 dark:text-slate-200">{{ $documentType->name }}</span>
        </nav>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Template {{ $documentType->name }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Setiap template memiliki bagian dan isian. Struktur inilah yang menjadi target
                    pemetaan hasil ekstraksi pada fase berikutnya.
                </p>
            </div>

            <a href="{{ route('admin.document-types.templates.create', $documentType) }}"
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                Tambah template
            </a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Nama</th>
                        <th scope="col" class="px-4 py-3 font-medium">Bagian</th>
                        <th scope="col" class="px-4 py-3 font-medium">Status</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($templates as $template)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $template->name }}</span>
                                @if ($template->description)
                                    <span class="block text-xs text-slate-500 dark:text-slate-400">
                                        {{ $template->description }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $template->sections_count }} bagian
                            </td>
                            <td class="px-4 py-3">
                                @if ($template->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-4">
                                    <a href="{{ route('admin.templates.edit', $template) }}"
                                        class="font-medium text-teal-700 hover:underline dark:text-teal-400">
                                        Susun struktur
                                    </a>

                                    <form method="POST" action="{{ route('admin.templates.destroy', $template) }}"
                                        x-data
                                        x-on:submit.prevent="if (confirm('Hapus template {{ $template->name }} beserta seluruh bagian dan isiannya?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')

                                        <x-danger-button class="px-3 py-1.5 text-xs">Hapus</x-danger-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                Belum ada template pada jenis dokumen ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $templates->links() }}
        </div>
    </div>
@endsection
