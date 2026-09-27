@extends('layouts.app')

@section('title', 'Ubah Pasien — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-bold tracking-tight">Ubah data pasien</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Perbarui data {{ $patient->name }}. Nomor rekam medis tidak dapat diubah.
        </p>

        <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            @include('patients.partials.form')
        </div>
    </div>
@endsection
