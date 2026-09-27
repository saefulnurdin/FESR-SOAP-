<section>
    <header>
        <h2 class="text-lg font-semibold">Ubah kata sandi</h2>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Gunakan kata sandi yang panjang dan unik. Semua sesi lain akan tetap aktif.
        </p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="current_password" value="Kata sandi saat ini" />
            <x-text-input id="current_password" name="current_password" type="password" class="mt-1"
                autocomplete="current-password" />
        </div>

        <div>
            <x-input-label for="new_password" value="Kata sandi baru" />
            <x-text-input id="new_password" name="password" type="password" class="mt-1"
                autocomplete="new-password" />
        </div>

        <div>
            <x-input-label for="new_password_confirmation" value="Ulangi kata sandi baru" />
            <x-text-input id="new_password_confirmation" name="password_confirmation" type="password" class="mt-1"
                autocomplete="new-password" />
        </div>

        <x-primary-button>Simpan kata sandi</x-primary-button>
    </form>
</section>
