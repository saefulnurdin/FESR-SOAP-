<form method="POST" action="{{ route('admin.templates.sections.store', $template) }}"
    class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
    @csrf

    <h2 class="text-base font-semibold">Tambah bagian</h2>
    <p class="-mt-2 text-sm text-slate-600 dark:text-slate-400">
        Bagian adalah pengelompokan isian, misalnya Subjective atau Objective pada catatan SOAP.
        Key dipakai sebagai penanda saat memetakan hasil ekstraksi, sehingga nilainya tetap walaupun
        judulnya diubah.
    </p>

    <div>
        <x-input-label for="section-title" value="Judul bagian" />
        <x-text-input id="section-title" name="title" type="text" class="mt-1" required
            placeholder="Subjective" :value="old('title')" />
    </div>

    <div>
        <x-input-label for="section-key" value="Key bagian" />
        <x-text-input id="section-key" name="key" type="text" class="mt-1 font-mono"
            placeholder="subjective" :value="old('key')" />
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            Huruf kecil, angka, dan garis bawah. Dikosongkan bila ingin diturunkan otomatis dari judul.
        </p>
    </div>

    <div>
        <x-input-label for="section-hint" value="Petunjuk pengisian" />
        <x-textarea id="section-hint" name="hint" rows="2"
            :value="old('hint')"
            placeholder="Contoh: keluhan dan riwayat yang diceritakan pasien atau keluarga." />
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            Petunjuk ini ikut dibaca layanan AI untuk memahami apa yang harus diambil dari rekaman.
        </p>
    </div>

    <x-primary-button>Tambah bagian</x-primary-button>
</form>
