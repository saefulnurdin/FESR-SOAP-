@php
    /** @var \App\Models\DocumentTemplate|null $template */
    $isCreating = $template === null;

    /*
     * Saat membuat, jenis dokumen dikirimkan oleh halaman pemanggil. Saat
     * menyunting, jenis dokumen diambil dari template yang sedang disunting.
     */
    $documentType = $isCreating ? $documentType : $template->documentType;
@endphp

<form method="POST"
    action="{{ $isCreating ? route('admin.document-types.templates.store', $documentType) : route('admin.templates.update', $template) }}"
    class="space-y-4">
    @csrf

    @unless ($isCreating)
        @method('PUT')
    @endunless

    <div>
        <x-input-label for="name" value="Nama template" />
        <x-text-input id="name" name="name" type="text" class="mt-1" required autofocus
            autocomplete="off" placeholder="SOAP Dewasa" :value="old('name', $template?->name)" />
    </div>

    <div>
        <x-input-label for="description" value="Keterangan" />
        <x-textarea id="description" name="description" rows="3"
            :value="old('description', $template?->description)" />
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            Sebutkan siapa yang boleh memakai template ini, misalnya hanya untuk pasien dewasa.
        </p>
    </div>

    <fieldset class="space-y-3 rounded-lg border border-slate-200 p-4 dark:border-slate-800">
        <legend class="px-1 text-sm font-medium">Status</legend>

        <label class="flex items-start gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1"
                @checked(old('is_active', $template?->is_active ?? true))
                class="mt-0.5 size-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-900">
            <span>
                <span class="font-medium">Template aktif</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">
                    Template nonaktif tidak ditawarkan saat membuat dokumen baru, tetapi strukturnya
                    tetap tersimpan.
                </span>
            </span>
        </label>
    </fieldset>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isCreating ? 'Buat template' : 'Simpan perubahan' }}</x-primary-button>

        <a href="{{ route('admin.document-types.templates.index', $documentType) }}"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            Batal
        </a>
    </div>
</form>
