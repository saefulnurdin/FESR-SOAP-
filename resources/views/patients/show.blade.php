@extends('layouts.app')

@section('title', $patient->name . ' — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    <a href="{{ route('patients.index') }}" class="hover:underline">Data pasien</a>
                    <span aria-hidden="true">/</span>
                    {{ $patient->name }}
                </p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight">{{ $patient->name }}</h1>
                <p class="mt-1 font-mono text-sm text-slate-600 dark:text-slate-400">
                    {{ $patient->medical_record_number ?? '-' }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('patients.edit', $patient) }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    Ubah data
                </a>

                <a href="{{ route('patients.encounters.create', $patient) }}"
                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    Catat kunjungan
                </a>
            </div>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @php
                $details = [
                    'Jenis kelamin' => $patient->gender->label(),
                    'Tanggal lahir' => $patient->birth_date?->format('d/m/Y'),
                    'Tempat lahir' => $patient->birth_place,
                    'Umur' => $patient->age !== null ? $patient->age.' tahun' : null,
                    'Golongan darah' => $patient->blood_type?->label(),
                    'NIK' => $patient->nik,
                    'Telepon' => $patient->phone,
                    'Alamat' => $patient->address,
                    'Terdaftar' => $patient->created_at->format('d/m/Y'),
                ];
            @endphp

            @foreach ($details as $label => $value)
                <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $label }}</dt>
                    <dd class="mt-1 text-sm">{{ $value ?? '-' }}</dd>
                </div>
            @endforeach
        </div>

        @if ($patient->allergies)
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900 dark:bg-rose-950">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-rose-800 dark:text-rose-200">
                    Riwayat alergi
                </h2>
                <p class="mt-1 text-sm whitespace-pre-line text-rose-900 dark:text-rose-100">{{ $patient->allergies }}</p>
            </div>
        @endif

        @if ($patient->notes)
            <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Catatan</h2>
                <p class="mt-1 text-sm whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $patient->notes }}</p>
            </div>
        @endif

        <div class="mt-10">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Riwayat kunjungan</h2>
                <span class="text-sm text-slate-500 dark:text-slate-400">
                    {{ $patient->encounters->count() }} kunjungan
                </span>
            </div>

            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Waktu</th>
                            <th scope="col" class="px-4 py-3 font-medium">Jenis</th>
                            <th scope="col" class="px-4 py-3 font-medium">Keluhan utama</th>
                            <th scope="col" class="px-4 py-3 font-medium">Petugas</th>
                            <th scope="col" class="px-4 py-3 font-medium">Status</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($patient->encounters as $encounter)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    {{ $encounter->occurred_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3">{{ $encounter->visit_type->label() }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ $encounter->chief_complaint ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ $encounter->doctor?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $encounter->status->badge() }}">
                                        {{ $encounter->status->label() }}
                                    </span>

                                    @if ($encounter->recordings()->exists())
                                        <span class="ml-2 text-xs text-slate-500 dark:text-slate-400">
                                            {{ $encounter->recordings()->count() }} rekaman
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-4">
                                        <a href="{{ route('patients.encounters.recordings.index', [$patient, $encounter]) }}"
                                            class="font-medium text-teal-700 hover:underline dark:text-teal-400">
                                            Rekaman
                                        </a>

                                        <a href="{{ route('patients.encounters.edit', [$patient, $encounter]) }}"
                                            class="font-medium text-teal-700 hover:underline dark:text-teal-400">
                                            Ubah
                                        </a>

                                        <form method="POST"
                                            action="{{ route('patients.encounters.destroy', [$patient, $encounter]) }}"
                                            x-data
                                            x-on:submit.prevent="if (confirm('Arsipkan kunjungan tanggal {{ $encounter->occurred_at->format('d/m/Y') }}?')) $el.submit()">
                                            @csrf
                                            @method('DELETE')

                                            <x-danger-button class="px-3 py-1.5 text-xs">Arsipkan</x-danger-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada kunjungan untuk pasien ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
