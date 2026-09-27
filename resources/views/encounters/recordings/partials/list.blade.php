{{-- Daftar rekaman satu kunjungan, dipakai untuk rekaman aktif dan arsip. --}}
<div class="mt-4 space-y-3">
    @forelse ($recordings as $recording)
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium">{{ $recording->created_at->translatedFormat('d M Y H:i') }}</span>

                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $recording->source->badge() }}">
                            {{ $recording->source->label() }}
                        </span>

                        @if ($recording->recordedBy)
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                oleh {{ $recording->recordedBy->name }}
                            </span>
                        @endif
                    </div>

                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ $recording->humanDuration() }} &middot; {{ $recording->humanSize() }}
                        @if ($recording->original_name)
                            &middot; {{ $recording->original_name }}
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    @if ($archived)
                        <form method="POST"
                            action="{{ route('patients.encounters.recordings.restore', [$recording->encounter->patient, $recording->encounter, $recording]) }}">
                            @csrf
                            <button type="submit"
                                class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                Kembalikan
                            </button>
                        </form>
                    @else
                        <form method="POST"
                            action="{{ route('patients.encounters.recordings.destroy', [$recording->encounter->patient, $recording->encounter, $recording]) }}"
                            onsubmit="return confirm('Arsipkan rekaman ini? Transkripnya tetap disimpan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                Arsipkan
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @unless ($recording->fileExists())
                <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-2 text-xs text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
                    Berkas rekaman tidak ditemukan di penyimpanan, sehingga tidak dapat diputar.
                </p>
            @else
                <audio controls preload="none" class="mt-3 w-full"
                    src="{{ route('patients.encounters.recordings.audio', [$recording->encounter->patient, $recording->encounter, $recording]) }}"></audio>
            @endunless

            <form method="POST" class="mt-4"
                action="{{ route('patients.encounters.recordings.transcript.update', [$recording->encounter->patient, $recording->encounter, $recording]) }}">
                @csrf
                @method('PATCH')

                <label for="transcript-{{ $recording->getKey() }}" class="block text-sm font-medium">Transkrip</label>
                <textarea id="transcript-{{ $recording->getKey() }}" name="transcript" rows="3"
                    class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 dark:border-slate-700 dark:bg-slate-800"
                    placeholder="Isi transkrip rekaman ini.">{{ $recording->transcript }}</textarea>

                <button type="submit"
                    class="mt-2 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-900 dark:bg-slate-200 dark:text-slate-900">
                    Simpan transkrip
                </button>
            </form>
        </div>
    @empty
        <p class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            Belum ada rekaman.
        </p>
    @endforelse
</div>
