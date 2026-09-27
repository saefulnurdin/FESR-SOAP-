@extends('layouts.guest')

@section('title', 'Kata Sandi Baru — ' . config('app.name'))
@section('heading', 'Buat kata sandi baru')

@section('description', 'Gunakan kata sandi yang panjang dan unik agar akun Anda tetap aman.')

@section('content')
    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1"
                :value="old('email', $request->email)" required autocomplete="username" />
        </div>

        <div>
            <x-input-label for="password" value="Kata sandi baru" />
            <x-text-input id="password" name="password" type="password" class="mt-1"
                required autocomplete="new-password" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Ulangi kata sandi baru" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1"
                required autocomplete="new-password" />
        </div>

        <div class="flex justify-end pt-2">
            <x-primary-button>Simpan kata sandi</x-primary-button>
        </div>
    </form>
@endsection
