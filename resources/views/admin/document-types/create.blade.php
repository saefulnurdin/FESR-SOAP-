@extends('layouts.app')

@section('title', 'Tambah Jenis Dokumen — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold tracking-tight">Tambah jenis dokumen</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Jenis dokumen mengelompokkan template. Buat jenis dokumen lebih dahulu, lalu tambahkan
            template beserta bagian dan isiannya.
        </p>

        <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            @include('admin.document-types.partials.form', ['documentType' => null])
        </div>
    </div>
@endsection
