@extends('layouts.app')

@section('title', 'Pengguna — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Manajemen pengguna</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Akun tenaga medis yang dapat masuk ke aplikasi. Akun baru dibuat di sini, tidak ada pendaftaran mandiri.
                </p>
            </div>

            <a href="{{ route('admin.users.create') }}"
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                Tambah pengguna
            </a>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="mt-6 flex flex-wrap items-center gap-2">
            <label for="search" class="sr-only">Cari pengguna</label>
            <x-text-input id="search" name="search" type="search" class="w-full sm:w-80"
                :value="request('search')" placeholder="Cari nama atau email" />
            <x-primary-button>Cari</x-primary-button>

            @if (request()->filled('search'))
                <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:underline dark:text-slate-400">
                    Reset
                </a>
            @endif
        </form>

        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Nama</th>
                        <th scope="col" class="px-4 py-3 font-medium">Email</th>
                        <th scope="col" class="px-4 py-3 font-medium">Peran</th>
                        <th scope="col" class="px-4 py-3 font-medium">Status</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3 font-medium">
                                {{ $user->name }}
                                @if ($user->is(auth()->user()))
                                    <span class="ml-1 text-xs font-normal text-slate-400">(Anda)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $user->email }}</td>
                            <td class="px-4 py-3">{{ $user->is_admin ? 'Administrator' : 'Petugas' }}</td>
                            <td class="px-4 py-3">
                                @if ($user->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-4">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="font-medium text-teal-700 hover:underline dark:text-teal-400">
                                        Ubah
                                    </a>

                                    @unless ($user->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            x-data
                                            x-on:submit.prevent="if (confirm('Hapus akun {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.')) $el.submit()">
                                            @csrf
                                            @method('DELETE')

                                            <x-danger-button class="px-3 py-1.5 text-xs">Hapus</x-danger-button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                {{ request()->filled('search') ? 'Pengguna tidak ditemukan.' : 'Belum ada pengguna.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
@endsection
