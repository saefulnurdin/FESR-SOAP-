@extends('layouts.app')

@section('title', 'Tambah Pasien — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-bold tracking-tight">Tambah pasien</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Nomor rekam medis dibuat otomatis setelah pasien terdaftar. NIK boleh dikosongkan bila belum tersedia.
        </p>

        <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            @include('patients.partials.form', ['patient' => null])
        </div>
    </div>
@endsection
