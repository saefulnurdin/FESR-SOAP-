@extends('layouts.app')

@section('title', 'Tambah Pengguna — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold tracking-tight">Tambah pengguna</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Buat akun untuk tenaga medis. Bagikan kata sandi awal secara pribadi, pengguna dapat menggantinya setelah masuk.
        </p>

        <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            @include('admin.users.partials.form', ['user' => null, 'isSelf' => false])
        </div>
    </div>
@endsection
