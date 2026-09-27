@extends('layouts.app')

@section('title', 'Dasbor — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-bold tracking-tight">Halo, {{ auth()->user()->name }}</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Anda masuk sebagai {{ auth()->user()->is_admin ? 'administrator' : 'petugas' }} di
            {{ config('fesr.institution') }}.
        </p>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            <a href="{{ route('patients.index') }}"
                class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-teal-400 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-teal-600">
                <p class="text-sm text-slate-600 dark:text-slate-400">Pasien terdaftar</p>
                <p class="mt-1 text-2xl font-semibold">{{ $patientCount }}</p>
            </a>

            <a href="{{ route('patients.index') }}"
                class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-teal-400 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-teal-600">
                <p class="text-sm text-slate-600 dark:text-slate-400">Kunjungan hari ini</p>
                <p class="mt-1 text-2xl font-semibold">{{ $todayEncounterCount }}</p>
            </a>

            <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm text-slate-600 dark:text-slate-400">Kunjungan berjalan</p>
                <p class="mt-1 text-2xl font-semibold">{{ $onGoingEncounterCount }}</p>
            </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <a href="{{ route('patients.index') }}"
                class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-teal-400 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-teal-600">
                <h2 class="font-semibold">Data pasien</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Cari, daftarkan, dan perbarui data pasien beserta riwayat kunjungan.
                </p>
            </a>

            <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-semibold">Dokumentasi suara</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Merekam kondisi pasien dengan suara sebagai draf SOAP. Fitur ini dibangun pada fase berikutnya.
                </p>
            </div>

            @can('manage-document-templates')
                <a href="{{ route('admin.document-types.index') }}"
                    class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-teal-400 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-teal-600">
                    <h2 class="font-semibold">Template dokumen</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        Susun bagian dan isian yang akan diisi hasil pemetaan, saat ini
                        {{ $documentTypeCount }} jenis dokumen.
                    </p>
                </a>
            @endcan

            @can('manage-users')
                <a href="{{ route('admin.users.index') }}"
                    class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-teal-400 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-teal-600">
                    <h2 class="font-semibold">Manajemen pengguna</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        Tambah, ubah, dan nonaktifkan akun tenaga medis.
                    </p>
                </a>
            @endcan

            <a href="{{ route('profile.edit') }}"
                class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-teal-400 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-teal-600">
                <h2 class="font-semibold">Profil saya</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Perbarui nama, email, dan kata sandi Anda.
                </p>
            </a>
        </div>
    </div>
@endsection
