{{--
    Panel perekaman.

    Batas durasi ditegakkan di peramban karena server tidak dapat memverifikasi
    panjang berkas tanpa ffmpeg; validasi server tetap ada sebagai jaring
    pengaman.
--}}
<div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"
    x-data="recorder({
        maxDuration: {{ (int) $maxDuration }},
        maxSizeKb: {{ (int) $maxSizeKb }},
    })"
    x-init="init()">

    @if ($encounter->status !== \App\Enums\EncounterStatus::Berjalan)
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
            Kunjungan ini sudah berstatus <strong>{{ $encounter->status->label() }}</strong>, sehingga rekaman
            baru tidak bisa ditambahkan. Rekaman yang ada masih dapat didengarkan dan diarsipkan.
        </div>
    @endif

    <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold">Rekam kondisi</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Maksimal {{ intdiv((int) $maxDuration, 60) }} menit atau
                {{ number_format((int) $maxSizeKb / 1024, 0, ',', '.') }} MB per berkas.
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if ($encounter->status === \App\Enums\EncounterStatus::Berjalan)
                <button type="button" x-show="state === 'idle'" x-cloak @click="start"
                    class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">
                    Mulai rekam
                </button>
            @endif
        </div>
    </div>

    {{-- Pemantau kondisi rekaman --}}
    <div class="mt-5 rounded-lg border border-slate-200 p-4 dark:border-slate-800" x-show="state !== 'idle'" x-cloak>
        <div class="flex items-center justify-between text-sm">
            <span class="font-medium" x-text="{
                recording: 'Merekam…',
                paused: 'Dijeda',
                ready: 'Siap diunggah',
            }[state]"></span>

            <span class="font-mono tabular-nums text-slate-600 dark:text-slate-300" x-text="formattedTime"></span>
        </div>

        {{-- Indikator level suara --}}
        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
            <div class="h-full rounded-full transition-[width] duration-75"
                :class="{
                    'bg-rose-500': level > 85,
                    'bg-teal-500': level <= 85,
                }"
                :style="`width: ${level}%`"
                x-bind:aria-label="`Level suara ${level} persen`"
                role="progressbar"
                :aria-valuenow="level"
                aria-valuemin="0"
                aria-valuemax="100"></div>
        </div>

        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400"
            x-show="state === 'recording'">
            Sisa waktu <span class="font-mono tabular-nums" x-text="remainingTime"></span>
        </p>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="button" x-show="state === 'recording'" x-cloak @click="pause"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                Jeda
            </button>

            <button type="button" x-show="state === 'paused'" x-cloak @click="resume"
                class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">
                Lanjutkan
            </button>

            <button type="button" x-show="['recording', 'paused'].includes(state)" x-cloak @click="stop"
                class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 dark:bg-slate-200 dark:text-slate-900">
                Selesai
            </button>

            <button type="button" x-show="['recording', 'paused'].includes(state)" x-cloak @click="discard"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                Buang
            </button>

            <button type="button" x-show="state === 'ready'" x-cloak @click="rerecord"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                Rekam ulang
            </button>
        </div>

        {{-- Tinjauan berkas sebelum diunggah --}}
        <div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-800" x-show="state === 'ready'" x-cloak>
            <audio :src="blobUrl" controls class="w-full"></audio>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                Ukuran <span x-text="file ? (file.size / 1024).toFixed(0) : 0"></span> KB
            </p>
        </div>
    </div>

    {{-- Pengiriman berkas --}}
    <form x-show="state === 'ready'" x-cloak x-ref="form" class="mt-5 border-t border-slate-200 pt-5 dark:border-slate-800"
        method="POST"
        action="{{ route('patients.encounters.recordings.store', [$encounter->patient, $encounter]) }}"
        data-redirect="{{ route('patients.encounters.recordings.index', [$encounter->patient, $encounter]) }}"
        @submit.prevent="submit">
        @csrf

        <div>
            <label for="transcript" class="block text-sm font-medium">Catatan atau transkrip</label>
            <textarea id="transcript" name="transcript" rows="4"
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800"
                placeholder="Tulis keluhan pasien di sini, atau biarkan kosong untuk diisi nanti.">{{ old('transcript') }}</textarea>
        </div>

        <input type="hidden" name="duration_seconds" :value="durationSeconds">
        <input type="hidden" name="captured_at" :value="new Date().toISOString()">

        <div class="mt-4 flex items-center gap-3">
            <button type="submit" :disabled="uploading"
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-50">
                <span x-show="!uploading">Simpan rekaman</span>
                <span x-show="uploading" x-cloak>Mengunggah <span x-text="progress"></span>%</span>
            </button>

            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800" x-show="uploading" x-cloak>
                <div class="h-full rounded-full bg-teal-500 transition-[width] duration-150" :style="`width: ${progress}%`"></div>
            </div>
        </div>
    </form>

    <div x-show="error" x-cloak
        class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200"
        x-text="error"
        role="alert"></div>

    <div x-show="autoStop" x-cloak
        class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
        Rekaman berhenti otomatis karena mencapai batas durasi.
    </div>

    @error('audio')
        <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
            {{ $message }}
        </div>
    @enderror
</div>
