@extends('layouts.guest')

@section('title', 'Lupa Kata Sandi — ' . config('app.name'))
@section('heading', 'Atur ulang kata sandi')

@section('description', 'Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.')

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1"
                :value="old('email')" required autofocus autocomplete="username" />
        </div>

        <div class="flex items-center justify-between gap-3 pt-2">
            <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:underline dark:text-slate-400">
                Kembali ke halaman masuk
            </a>

            <x-primary-button>Kirim tautan</x-primary-button>
        </div>
    </form>
@endsection
