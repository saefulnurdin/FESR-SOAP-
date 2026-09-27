@extends('layouts.app')

@section('title', 'Ubah Pengguna — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold tracking-tight">Ubah pengguna</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Perbarui data akun {{ $user->name }}. Kosongkan kata sandi bila tidak ingin mengubahnya.
        </p>

        <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            @include('admin.users.partials.form')
        </div>
    </div>
@endsection
