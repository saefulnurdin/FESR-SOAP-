@extends('layouts.app')

@section('title', 'Pasien — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $archived ? 'Arsip pasien' : 'Data pasien' }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    {{ $archived
                        ? 'Pasien yang diarsipkan tetap menyimpan seluruh riwayat klinisnya dan dapat dipulihkan.'
                        : 'Pencatatan pasien dan riwayat kunjungan.' }}
                </p>
            </div>

            <a href="{{ route('patients.create') }}"
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                Tambah pasien
            </a>
        </div>

        <div class="mt-6 flex flex-wrap gap-2 text-sm">
            <a href="{{ route('patients.index', request()->except('archived')) }}"
                @class([
                    'rounded-lg px-3 py-1.5 font-medium transition',
                    'bg-slate-900 text-white dark:bg-white dark:text-slate-900' => ! $archived,
                    'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => $archived,
                ])>
                Pasien aktif
            </a>

            <a href="{{ route('patients.index', array_merge(request()->except('archived'), ['archived' => 1])) }}"
                @class([
                    'rounded-lg px-3 py-1.5 font-medium transition',
                    'bg-slate-900 text-white dark:bg-white dark:text-slate-900' => $archived,
                    'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! $archived,
                ])>
                Arsip
            </a>
        </div>

        <form method="GET" action="{{ route('patients.index') }}" class="mt-6 flex flex-wrap items-center gap-2">
            @if ($archived)
                <input type="hidden" name="archived" value="1">
            @endif

            <label for="search" class="sr-only">Cari pasien</label>
            <x-text-input id="search" name="search" type="search" class="w-full sm:w-72"
                :value="request('search')" placeholder="Cari nama, nomor rekam medis, NIK, atau telepon" />

            <label for="gender" class="sr-only">Saring jenis kelamin</label>
            <x-select id="gender" name="gender" class="w-full sm:w-48" :options="\App\Enums\Gender::options()"
                :selected="request('gender', '')" />

            <x-primary-button>Cari</x-primary-button>

            @if (request()->filled('search') || request()->filled('gender'))
                <a href="{{ route('patients.index', $archived ? ['archived' => 1] : []) }}"
                    class="text-sm text-slate-500 hover:underline dark:text-slate-400">
                    Reset
                </a>
            @endif
        </form>

        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">No. Rekam Medis</th>
                        <th scope="col" class="px-4 py-3 font-medium">Nama</th>
                        <th scope="col" class="px-4 py-3 font-medium">Jenis kelamin</th>
                        <th scope="col" class="px-4 py-3 font-medium">Umur</th>
                        <th scope="col" class="px-4 py-3 font-medium">NIK</th>
                        <th scope="col" class="px-4 py-3 font-medium">Telepon</th>
                        <th scope="col" class="px-4 py-3 font-medium">Kunjungan</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($patients as $patient)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-400">
                                {{ $patient->medical_record_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('patients.show', $patient) }}"
                                    class="text-teal-700 hover:underline dark:text-teal-400">
                                    {{ $patient->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3">{{ $patient->gender->label() }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $patient->age !== null ? $patient->age.' th' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $patient->nik ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $patient->phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $patient->encounters_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-4">
                                    @if ($patient->trashed())
                                        <form method="POST" action="{{ route('patients.restore', $patient) }}">
                                            @csrf
                                            <button type="submit" class="font-medium text-teal-700 hover:underline dark:text-teal-400">
                                                Pulihkan
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('patients.edit', $patient) }}"
                                            class="font-medium text-teal-700 hover:underline dark:text-teal-400">
                                            Ubah
                                        </a>

                                        <form method="POST" action="{{ route('patients.destroy', $patient) }}"
                                            x-data
                                            x-on:submit.prevent="if (confirm('Arsipkan pasien {{ $patient->name }}? Riwayat kunjungan tidak akan hilang dan dapat dipulihkan.')) $el.submit()">
                                            @csrf
                                            @method('DELETE')

                                            <x-danger-button class="px-3 py-1.5 text-xs">Arsipkan</x-danger-button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                @if ($archived)
                                    Belum ada pasien yang diarsipkan.
                                @elseif (request()->filled('search') || request()->filled('gender'))
                                    Pasien tidak ditemukan.
                                @else
                                    Belum ada pasien. Tambahkan pasien untuk mulai mencatat kunjungan.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $patients->links() }}
        </div>
    </div>
@endsection
