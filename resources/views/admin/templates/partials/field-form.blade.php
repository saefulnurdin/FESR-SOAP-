@php
    use App\Enums\DocumentFieldType;

    /** @var \App\Models\DocumentTemplate $template */
    /** @var \Illuminate\Support\Collection<int, \App\Models\DocumentTemplateSection> $sections */
    /** @var \App\Models\DocumentTemplateField|null $field */
    $isCreating = $field === null;

    /*
     * Opsi dikirim sebagai satu baris per pilihan. Setelah validasi gagal
     * nilainya sudah berupa array, jadi diserialikan kembali ke teks.
     */
    $oldOptions = old('options');
    $optionsValue = match (true) {
        is_array($oldOptions) => implode("\n", $oldOptions),
        is_string($oldOptions) && $oldOptions !== '' => $oldOptions,
        default => $field?->options === null ? null : implode("\n", $field->options),
    };
@endphp

<form method="POST"
    action="{{ $isCreating ? route('admin.templates.fields.store', $template) : route('admin.templates.fields.update', [$template, $field]) }}"
    class="space-y-4"
    x-data="{ type: '{{ old('type', $field?->type?->value ?? DocumentFieldType::Text->value) }}' }">
    @csrf

    @unless ($isCreating)
        @method('PUT')
    @endunless

    <div>
        <x-input-label for="field-section" value="Bagian" />
        <x-select id="field-section" name="document_template_section_id" class="mt-1" required
            :options="['' => '— Pilih bagian —'] + $sections->mapWithKeys(fn ($item): array => [$item->id => $item->title])->all()"
            :selected="old('document_template_section_id', $section?->id)" />
    </div>

    <div>
        <x-input-label for="field-label" value="Nama isian" />
        <x-text-input id="field-label" name="label" type="text" class="mt-1" required
            placeholder="Tekanan darah" :value="old('label', $field?->label)" />
    </div>

    <div>
        <x-input-label for="field-key" value="Key isian" />
        <x-text-input id="field-key" name="key" type="text" class="mt-1 font-mono"
            placeholder="tekanan_darah" :value="old('key', $field?->key)" />
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            Huruf kecil, angka, dan garis bawah. Target pemetaan hasil ekstraksi, jadi sebaiknya
            tidak diubah setelah rekaman terisi. Dikosongkan bila ingin diturunkan dari nama isian.
        </p>
    </div>

    <div>
        <x-input-label for="field-type" value="Tipe isian" />
        <x-select id="field-type" name="type" class="mt-1" required x-model="type"
            :options="DocumentFieldType::options()" :selected="old('type', $field?->type?->value)" />
    </div>

    <div x-show="type === 'select'" x-cloak>
        <x-input-label for="field-options" value="Daftar pilihan" />
        <x-textarea id="field-options" name="options" rows="4"
            :value="$optionsValue" placeholder="Ya&#10;Tidak" />
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            Satu pilihan per baris. Wajib diisi untuk isian bertipe Pilihan.
        </p>
    </div>

    <div>
        <x-input-label for="field-unit" value="Satuan" />
        <x-text-input id="field-unit" name="unit" type="text" class="mt-1" placeholder="mmHg"
            :value="old('unit', $field?->unit)" />
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            Boleh dikosongkan. Disimpan terpisah supaya hasil ekstraksi dapat dicocokkan dengan
            satuan yang benar.
        </p>
    </div>

    <fieldset class="space-y-3 rounded-lg border border-slate-200 p-4 dark:border-slate-800">
        <legend class="px-1 text-sm font-medium">Keterangan</legend>

        <label class="flex items-start gap-2 text-sm">
            <input type="checkbox" name="is_required" value="1"
                @checked(old('is_required', $field?->is_required ?? false))
                class="mt-0.5 size-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-900">
            <span>
                <span class="font-medium">Wajib diisi</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">
                    Dokumen tidak dapat difinalisasi selama isian ini masih kosong.
                </span>
            </span>
        </label>
    </fieldset>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isCreating ? 'Tambah isian' : 'Simpan perubahan' }}</x-primary-button>

        <a href="{{ route('admin.templates.edit', $template) }}"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            Batal
        </a>
    </div>
</form>
