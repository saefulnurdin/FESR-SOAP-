@extends('layouts.app')

@section('title', 'Tambah Template — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <nav class="text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('admin.document-types.index') }}" class="hover:underline">Jenis dokumen</a>
            <span class="mx-1">/</span>
            <a href="{{ route('admin.document-types.templates.index', $documentType) }}" class="hover:underline">
                {{ $documentType->name }}
            </a>
        </nav>

        <h1 class="mt-4 text-2xl font-bold tracking-tight">Tambah template</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Setelah template dibuat, Anda akan langsung diarahkan ke halaman penyusun struktur
            untuk menambahkan bagian dan isian.
        </p>

        <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            @include('admin.templates.partials.form', ['template' => null])
        </div>
    </div>
@endsection
