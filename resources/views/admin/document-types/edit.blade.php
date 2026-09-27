@extends('layouts.app')

@section('title', 'Ubah Jenis Dokumen — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold tracking-tight">Ubah jenis dokumen</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Perbarui data jenis dokumen {{ $documentType->name }}. Kode dipakai pada integrasi
            layanan AI, jadi sebaiknya tidak diubah setelah template mulai memakai.
        </p>

        <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            @include('admin.document-types.partials.form')
        </div>

        <div class="mt-4">
            <a href="{{ route('admin.document-types.templates.index', $documentType) }}"
                class="text-sm font-medium text-teal-700 hover:underline dark:text-teal-400">
                Lihat {{ $documentType->templates()->count() }} template milik jenis dokumen ini
            </a>
        </div>
    </div>
@endsection
