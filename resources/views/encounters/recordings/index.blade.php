@extends('layouts.app')

@section('title', 'Rekaman — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Rekaman kunjungan</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    {{ $encounter->patient->name }} &middot;
                    {{ $encounter->visit_type->label() }} &middot;
                    {{ $encounter->occurred_at->translatedFormat('d M Y H:i') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                @can('update', $encounter)
                    <a href="{{ route('patients.encounters.edit', [$encounter->patient, $encounter]) }}"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        Ubah kunjungan
                    </a>
                @endcan

                <a href="{{ route('patients.show', $encounter->patient) }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    Kembali ke pasien
                </a>
            </div>
        </div>

        @include('encounters.recordings.partials.recorder')

        <section class="mt-8">
            <h2 class="text-lg font-semibold">Rekaman tersimpan</h2>

            @include('encounters.recordings.partials.list', ['recordings' => $recordings, 'archived' => false])

            @if ($archivedRecordings->isNotEmpty())
                <details class="mt-4 rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <summary class="cursor-pointer px-4 py-3 text-sm font-medium text-slate-600 dark:text-slate-300">
                        Arsip ({{ $archivedRecordings->count() }})
                    </summary>

                    <div class="border-t border-slate-200 px-4 py-4 dark:border-slate-800">
                        @include('encounters.recordings.partials.list', ['recordings' => $archivedRecordings, 'archived' => true])
                    </div>
                </details>
            @endif
        </section>
    </div>
@endsection
