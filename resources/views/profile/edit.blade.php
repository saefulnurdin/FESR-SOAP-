@extends('layouts.app')

@section('title', 'Profil Saya — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-bold tracking-tight">Profil saya</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Data akun yang Anda gunakan untuk masuk ke aplikasi.
        </p>

        <div class="mt-8 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
@endsection
