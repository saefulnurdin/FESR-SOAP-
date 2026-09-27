@use('App\Enums\BloodType')
@use('App\Enums\Gender')

@php
    /** @var \App\Models\Patient|null $patient */
    $isCreating = $patient === null;
@endphp

<form method="POST"
    action="{{ $isCreating ? route('patients.store') : route('patients.update', $patient) }}"
    class="space-y-6">
    @csrf

    @unless ($isCreating)
        @method('PUT')
    @endunless

    <fieldset class="space-y-4">
        <legend class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
            Identitas
        </legend>

        <div>
            <x-input-label for="name" value="Nama lengkap" />
            <x-text-input id="name" name="name" type="text" class="mt-1" required autofocus
                autocomplete="off" :value="old('name', $patient?->name)" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="nik" value="NIK" />
                <x-text-input id="nik" name="nik" type="text" inputmode="numeric" maxlength="16" class="mt-1"
                    autocomplete="off" :value="old('nik', $patient?->nik)" placeholder="16 digit" />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Opsional. Kosongkan bila pasien tidak memiliki NIK.
                </p>
            </div>

            <div>
                <x-input-label for="gender" value="Jenis kelamin" />
                <x-select id="gender" name="gender" class="mt-1" required
                    :options="Gender::options()"
                    :selected="old('gender', $patient?->gender?->value ?? Gender::LakiLaki->value)" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="birth_date" value="Tanggal lahir" />
                <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1"
                    autocomplete="off" :value="old('birth_date', $patient?->birth_date?->format('Y-m-d'))" />
            </div>

            <div>
                <x-input-label for="birth_place" value="Tempat lahir" />
                <x-text-input id="birth_place" name="birth_place" type="text" class="mt-1"
                    autocomplete="off" :value="old('birth_place', $patient?->birth_place)" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="phone" value="Nomor telepon" />
                <x-text-input id="phone" name="phone" type="text" inputmode="tel" class="mt-1"
                    autocomplete="off" :value="old('phone', $patient?->phone)" placeholder="08xxxxxxxxxx" />
            </div>

            <div>
                <x-input-label for="blood_type" value="Golongan darah" />
                <x-select id="blood_type" name="blood_type" class="mt-1"
                    :options="BloodType::options()"
                    :selected="old('blood_type', $patient?->blood_type?->value)" />
            </div>
        </div>
    </fieldset>

    <fieldset class="space-y-4 border-t border-slate-200 pt-6 dark:border-slate-800">
        <legend class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
            Kontak dan medis
        </legend>

        <div>
            <x-input-label for="address" value="Alamat" />
            <x-textarea id="address" name="address" :value="old('address', $patient?->address)" />
        </div>

        <div>
            <x-input-label for="allergies" value="Riwayat alergi" />
            <x-textarea id="allergies" name="allergies"
                placeholder="Tuliskan obat atau bahan yang menimbulkan reaksi, atau TIDAK DIKETAHUI bila belum ada data."
                :value="old('allergies', $patient?->allergies)" />
        </div>

        <div>
            <x-input-label for="notes" value="Catatan" />
            <x-textarea id="notes" name="notes" :value="old('notes', $patient?->notes)" />
        </div>
    </fieldset>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isCreating ? 'Daftarkan pasien' : 'Simpan perubahan' }}</x-primary-button>

        <a href="{{ $isCreating ? route('patients.index') : route('patients.show', $patient) }}"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            Batal
        </a>
    </div>
</form>
