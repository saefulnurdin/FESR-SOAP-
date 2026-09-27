@extends('layouts.app')

@section('title', 'Catat Kunjungan — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold tracking-tight">Catat kunjungan</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Kunjungan atas nama {{ $patient->name }} ({{ $patient->medical_record_number ?? '-' }}).
            Petugas dicatat otomatis dari akun yang sedang masuk.
        </p>

        <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            @include('encounters.partials.form', ['encounter' => null])
        </div>
    </div>
@endsection
