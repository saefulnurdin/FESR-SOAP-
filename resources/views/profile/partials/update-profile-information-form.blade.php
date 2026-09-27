<section>
    <header>
        <h2 class="text-lg font-semibold">Informasi profil</h2>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Nama dan email yang ditampilkan pada dokumen klinis yang Anda susun.
        </p>
    </header>

    <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('PATCH')

        <div>
            <x-input-label for="name" value="Nama lengkap" />
            <x-text-input id="name" name="name" type="text" class="mt-1"
                :value="old('name', $user->name)" required autocomplete="name" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1"
                :value="old('email', $user->email)" required autocomplete="username" />
        </div>

        <div class="rounded-lg bg-slate-50 p-4 text-sm dark:bg-slate-800/60">
            <p class="font-medium">Peran akun</p>
            <p class="mt-0.5 text-slate-600 dark:text-slate-400">
                {{ $user->is_admin ? 'Administrator' : 'Petugas' }} &middot;
                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}.
                Hubungi administrator {{ config('fesr.institution') }} untuk mengubah peran.
            </p>
        </div>

        <x-primary-button>Simpan profil</x-primary-button>
    </form>
</section>
