@use('App\Enums\EncounterStatus')
@use('App\Enums\VisitType')

@php
    /** @var \App\Models\Encounter|null $encounter */
    $isCreating = $encounter === null;
@endphp

<form method="POST"
    action="{{ $isCreating
        ? route('patients.encounters.store', $patient)
        : route('patients.encounters.update', [$patient, $encounter]) }}"
    class="space-y-4">
    @csrf

    @unless ($isCreating)
        @method('PUT')
    @endunless

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="occurred_at" value="Waktu kunjungan" />
            <x-text-input id="occurred_at" name="occurred_at" type="datetime-local" class="mt-1" required
                :value="old('occurred_at', $encounter?->occurred_at?->format('Y-m-d\TH:i'))" />
        </div>

        <div>
            <x-input-label for="visit_type" value="Jenis kunjungan" />
            <x-select id="visit_type" name="visit_type" class="mt-1" required
                :options="VisitType::options()"
                :selected="old('visit_type', $encounter?->visit_type?->value ?? VisitType::RawatJalan->value)" />
        </div>
    </div>

    <div>
        <x-input-label for="status" value="Status" />
        <x-select id="status" name="status" class="mt-1" required
            :options="EncounterStatus::options()"
            :selected="old('status', $encounter?->status?->value ?? EncounterStatus::Berjalan->value)" />
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            Kunjungan berstatus berjalan masih dapat diisi rekaman suara dan draf SOAP pada fase berikutnya.
        </p>
    </div>

    <div>
        <x-input-label for="chief_complaint" value="Keluhan utama" />
        <x-textarea id="chief_complaint" name="chief_complaint"
            :value="old('chief_complaint', $encounter?->chief_complaint)" />
    </div>

    <div>
        <x-input-label for="notes" value="Catatan" />
        <x-textarea id="notes" name="notes" :value="old('notes', $encounter?->notes)" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $isCreating ? 'Simpan kunjungan' : 'Simpan perubahan' }}</x-primary-button>

        <a href="{{ route('patients.show', $patient) }}"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            Batal
        </a>
    </div>
</form>
