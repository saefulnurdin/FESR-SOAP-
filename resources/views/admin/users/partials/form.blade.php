@php
    /** @var \App\Models\User|null $user */
    $isCreating = $user === null;
@endphp

<form method="POST"
    action="{{ $isCreating ? route('admin.users.store') : route('admin.users.update', $user) }}"
    class="space-y-4">
    @csrf

    @unless ($isCreating)
        @method('PUT')
    @endunless

    <div>
        <x-input-label for="name" value="Nama lengkap" />
        <x-text-input id="name" name="name" type="text" class="mt-1" required autofocus
            autocomplete="off" :value="old('name', $user?->name)" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1" required
            autocomplete="off" :value="old('email', $user?->email)" />
    </div>

    <div>
        <x-input-label for="password" :value="$isCreating ? 'Kata sandi' : 'Kata sandi baru'" />
        <x-text-input id="password" name="password" type="password" class="mt-1"
            :required="$isCreating" autocomplete="new-password" />
        @unless ($isCreating)
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Biarkan kosong bila kata sandi tidak diubah.</p>
        @endunless
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Ulangi kata sandi" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1"
            :required="$isCreating" autocomplete="new-password" />
    </div>

    <fieldset class="space-y-3 rounded-lg border border-slate-200 p-4 dark:border-slate-800">
        <legend class="px-1 text-sm font-medium">Hak akses</legend>

        <label class="flex items-start gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1"
                @checked(old('is_active', $user?->is_active ?? true))
                @disabled($isSelf)
                class="mt-0.5 size-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-900">
            <span>
                <span class="font-medium">Akun aktif</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">
                    Akun nonaktif tidak dapat masuk dan langsung kehilangan akses bila sedang masuk.
                </span>
            </span>
        </label>

        <label class="flex items-start gap-2 text-sm">
            <input type="checkbox" name="is_admin" value="1"
                @checked(old('is_admin', $user?->is_admin ?? false))
                @disabled($isSelf)
                class="mt-0.5 size-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-900">
            <span>
                <span class="font-medium">Administrator</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">
                    Administrator dapat menambah, mengubah, dan menonaktifkan pengguna lain.
                </span>
            </span>
        </label>

        @if ($isSelf)
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Hak akses akun Anda sendiri tidak dapat diubah dari halaman ini.
            </p>
        @endif
    </fieldset>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isCreating ? 'Buat akun' : 'Simpan perubahan' }}</x-primary-button>

        <a href="{{ route('admin.users.index') }}"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            Batal
        </a>
    </div>
</form>
