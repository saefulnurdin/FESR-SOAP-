@extends('layouts.app')

@section('title', 'Perangkat Perekam — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-5xl">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Perangkat perekam</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Perangkat ESP32 yang dapat mengirim rekaman ke aplikasi tanpa peramban. Token hanya
                ditampilkan satu kali, sedangkan aplikasi menyimpannya sebagai hash.
            </p>
        </div>

        {{-- Token yang baru diterbitkan, hanya muncul sekali --}}
        @if (session('device_token'))
            <div class="mt-6 rounded-xl border border-teal-300 bg-teal-50 p-4 dark:border-teal-800 dark:bg-teal-950">
                <h2 class="text-sm font-semibold text-teal-900 dark:text-teal-100">
                    Simpan token ini sekarang
                </h2>
                <p class="mt-1 text-sm text-teal-800 dark:text-teal-200">
                    Token tidak dapat dilihat lagi. Kalau hilang, terbitkan token baru dari daftar di bawah.
                </p>

                <code class="mt-3 block break-all rounded-lg bg-white p-3 font-mono text-xs text-slate-900 dark:bg-slate-900 dark:text-slate-100">
                    {{ session('device_token') }}
                </code>

                <button type="button" class="mt-3 rounded-lg border border-teal-600 px-3 py-1.5 text-xs font-medium text-teal-800 hover:bg-teal-100 dark:text-teal-200 dark:hover:bg-teal-900"
                    onclick="navigator.clipboard.writeText(this.previousElementSibling.textContent.trim()); this.textContent = 'Token disalin'">
                    Salin token
                </button>
            </div>
        @endif

        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Daftarkan perangkat</h2>

            <form method="POST" action="{{ route('admin.devices.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium">Nama perangkat</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                        class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800"
                        placeholder="Perekam Poli Dalam">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="user_id" class="block text-sm font-medium">Pemilik</label>
                    <select id="user_id" name="user_id" required
                        class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800">
                        <option value="">Pilih petugas</option>
                        @foreach ($owners as $owner)
                            <option value="{{ $owner->id }}" @selected(old('user_id') == $owner->id)>
                                {{ $owner->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <button type="submit"
                        class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                        Daftarkan dan terbitkan token
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Perangkat</th>
                        <th scope="col" class="px-4 py-3 font-medium">Pemilik</th>
                        <th scope="col" class="px-4 py-3 font-medium">Terakhir aktif</th>
                        <th scope="col" class="px-4 py-3 font-medium">Status</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($devices as $device)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $device->name }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $device->user->name }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                                @if ($device->wasSeenRecently())
                                    <span class="text-teal-600 dark:text-teal-400">Aktif baru saja</span>
                                @elseif ($device->last_seen_at)
                                    {{ $device->last_seen_at->diffForHumans() }}
                                @else
                                    Belum pernah
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs font-medium',
                                    'bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-200' => $device->is_active,
                                    'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => ! $device->is_active,
                                ])>
                                    {{ $device->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.devices.toggle', $device) }}">
                                        @csrf
                                        <button type="submit"
                                            class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                            {{ $device->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.devices.token.store', $device) }}"
                                        onsubmit="return confirm('Terbitkan token baru? Token lama langsung tidak berlaku.')">
                                        @csrf
                                        <button type="submit"
                                            class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                            Token baru
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.devices.destroy', $device) }}"
                                        onsubmit="return confirm('Hapus perangkat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50 dark:border-rose-800 dark:text-rose-400 dark:hover:bg-rose-950">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                Belum ada perangkat terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Cara perangkat mengirim rekaman</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                Perangkat memanggil dua endpoint berikut. Token dikirim pada setiap permintaan sebagai header
                <code class="font-mono text-xs">X-Device-Token</code>.
            </p>

            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="font-medium">Cek token dan pemilik</dt>
                    <dd class="mt-1 rounded-lg bg-slate-100 p-3 font-mono text-xs dark:bg-slate-800">
                        GET {{ url('/api/device') }}
                    </dd>
                </div>

                <div>
                    <dt class="font-medium">Kirim rekaman ke sebuah kunjungan</dt>
                    <dd class="mt-1 rounded-lg bg-slate-100 p-3 font-mono text-xs dark:bg-slate-800">
                        POST {{ url('/api/encounters/{encounter}/recordings') }}<br>
                        multipart/form-data: <strong>audio</strong>, duration_seconds, captured_at
                    </dd>
                </div>
            </dl>

            <p class="mt-4 text-sm text-slate-600 dark:text-slate-400">
                Kunjungan harus berstatus berjalan, dan hanya pemilik perangkat yang boleh mengirim rekaman
                untuk kunjungan miliknya sendiri.
            </p>
        </div>
    </div>
@endsection
