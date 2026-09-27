@extends('layouts.guest')

@section('title', 'Masuk — ' . config('app.name'))
@section('heading', 'Masuk ke akun Anda')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1"
                :value="old('email')" required autofocus autocomplete="username" />
        </div>

        <div>
            <x-input-label for="password" value="Kata sandi" />
            <x-text-input id="password" name="password" type="password" class="mt-1"
                required autocomplete="current-password" />
        </div>

        <label for="remember" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
            <input id="remember" type="checkbox" name="remember" value="1"
                class="size-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-900">
            Ingat saya di perangkat ini
        </label>

        <div class="flex items-center justify-between gap-3 pt-2">
            <a href="{{ route('password.request') }}" class="text-sm text-teal-700 hover:underline dark:text-teal-400">
                Lupa kata sandi?
            </a>

            <x-primary-button>Masuk</x-primary-button>
        </div>
    </form>

    <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">
        Akun dibuat oleh administrator {{ config('fesr.institution') }}.
    </p>
@endsection
