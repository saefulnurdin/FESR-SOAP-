@php
    /** @var \App\Models\DocumentType|null $documentType */
    $isCreating = $documentType === null;
@endphp

<form method="POST"
    action="{{ $isCreating ? route('admin.document-types.store') : route('admin.document-types.update', $documentType) }}"
    class="space-y-4">
    @csrf

    @unless ($isCreating)
        @method('PUT')
    @endunless

    <div>
        <x-input-label for="code" value="Kode" />
        <x-text-input id="code" name="code" type="text" class="mt-1 font-mono" required autofocus
            autocomplete="off" placeholder="soap" :value="old('code', $documentType?->code)" />
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            Huruf kecil, angka, dan garis bawah. Dipakai pada alamat halaman dan integrasi ke
            layanan AI, misalnya <span class="font-mono">soap</span>.
        </p>
    </div>

    <div>
        <x-input-label for="name" value="Nama jenis dokumen" />
        <x-text-input id="name" name="name" type="text" class="mt-1" required
            autocomplete="off" :value="old('name', $documentType?->name)" />
    </div>

    <div>
        <x-input-label for="description" value="Keterangan" />
        <x-textarea id="description" name="description" rows="3"
            :value="old('description', $documentType?->description)" />
    </div>

    <fieldset class="space-y-3 rounded-lg border border-slate-200 p-4 dark:border-slate-800">
        <legend class="px-1 text-sm font-medium">Status</legend>

        <label class="flex items-start gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1"
                @checked(old('is_active', $documentType?->is_active ?? true))
                class="mt-0.5 size-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-900">
            <span>
                <span class="font-medium">Jenis dokumen aktif</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">
                    Jenis dokumen nonaktif disembunyikan dari pilihan saat membuat dokumen baru.
                </span>
            </span>
        </label>
    </fieldset>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isCreating ? 'Buat jenis dokumen' : 'Simpan perubahan' }}</x-primary-button>

        <a href="{{ route('admin.document-types.index') }}"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            Batal
        </a>
    </div>
</form>
