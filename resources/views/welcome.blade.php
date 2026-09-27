@extends('layouts.app')

@section('title', config('app.name').' — Beranda')

@section('content')
    <div class="mx-auto max-w-3xl py-8 text-center">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">
            {{ config('app.name') }}
        </h1>

        <p class="mt-3 text-base text-slate-600 dark:text-slate-400">
            Front-End Speech Recognition untuk Dokumentasi Klinis Berbasis Web
        </p>

        <div class="mt-10 grid gap-4 text-left sm:grid-cols-2">
            @foreach ([
                ['title' => 'Dokumentasi suara', 'body' => 'Tenaga medis mendokumentasikan kondisi pasien dengan suara, tanpa mengetik manual.'],
                ['title' => 'Transkripsi lokal', 'body' => 'Whisper dijalankan secara lokal melalui AI Service Python, tanpa API berbayar.'],
                ['title' => 'Ekstraksi entitas', 'body' => 'Tahap NLU mengenali tanggal, dosis, gejala, dan diagnosis dari transkrip.'],
                ['title' => 'Dokumen SOAP', 'body' => 'Entitas klinis dipetakan ke template SOAP sebagai draf yang dapat ditinjau.'],
            ] as $feature)
                <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="font-semibold">{{ $feature['title'] }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $feature['body'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-10 rounded-xl border border-amber-200 bg-amber-50 p-5 text-left text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100">
            <p class="font-semibold">Status: Fase 1 — Autentikasi &amp; manajemen pengguna</p>
            <p class="mt-1">
                Sudah tersedia: masuk, lupa kata sandi, profil sendiri, serta pengelolaan akun oleh administrator.
                Manajemen pasien dan alur SOAP dibangun pada fase berikutnya.
            </p>
        </div>

        <div class="mt-6 flex justify-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    Buka dasbor
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    Masuk
                </a>
            @endauth
        </div>
    </div>
@endsection
