<?php

namespace App\Http\Controllers;

use App\Enums\EncounterStatus;
use App\Http\Requests\StoreRecordingRequest;
use App\Models\Encounter;
use App\Models\EncounterRecording;
use App\Models\Patient;
use App\Services\RecordingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Rekaman kondisi pasien untuk satu kunjungan.
 *
 * Berkas audio tidak disimpan di dalam database dan tidak punya URL publik.
 * Pemutaran hanya lewat method audio() supaya setiap permintaan diperiksa
 * terhadap petugas yang menangani kunjungan tersebut. Hak aksesnya dipasang
 * di routes/web.php lewat middleware can:.
 */
class EncounterRecordingController extends Controller
{
    /**
     * Show the recorder along with the recordings already made.
     */
    public function index(Patient $patient, Encounter $encounter): View
    {
        return view('encounters.recordings.index', [
            'encounter' => $encounter,
            'recordings' => $encounter->recordings()
                ->with('recordedBy')
                ->latest()
                ->get(),
            'archivedRecordings' => $encounter->recordings()->onlyTrashed()->get(),
            'maxDuration' => (int) config('fesr.audio.max_duration_seconds'),
            'maxSizeKb' => (int) config('fesr.audio.max_size_kb'),
        ]);
    }

    /**
     * Store an uploaded recording.
     */
    public function store(StoreRecordingRequest $request, Patient $patient, Encounter $encounter, RecordingService $recordings): RedirectResponse
    {
        if ($encounter->status !== EncounterStatus::Berjalan) {
            return back()->with('error', 'Rekaman hanya bisa ditambahkan pada kunjungan yang sedang berjalan.');
        }

        $recording = $recordings->store(
            encounter: $encounter,
            audio: $request->file('audio'),
            recordedBy: $request->user(),
            durationSeconds: $request->integer('duration_seconds') ?: null,
            capturedAt: $request->input('captured_at'),
            transcript: $request->input('transcript'),
        );

        return back()->with('success', 'Rekaman tersimpan.');
    }

    /**
     * Update the transcript of a recording.
     */
    public function updateTranscript(Request $request, Patient $patient, Encounter $encounter, EncounterRecording $recording): RedirectResponse
    {
        $validated = $request->validate([
            'transcript' => ['nullable', 'string', 'max:10000'],
        ], [
            'transcript.max' => 'Transkrip maksimal 10.000 karakter.',
        ], [
            'transcript' => 'transkrip',
        ]);

        $recording->update($validated);

        return back()->with('success', 'Transkrip disimpan.');
    }

    /**
     * Stream the audio file of a recording.
     *
     * Dipakai response() dari disk agar berkas besar tidak ikut ke memori dan
     * rentang byte tetap bisa dipakai peramban untuk memulai di tengah rekaman.
     */
    public function audio(Patient $patient, Encounter $encounter, EncounterRecording $recording): RedirectResponse|StreamedResponse
    {
        $disk = Storage::disk($recording->storage_disk);

        if (! $disk->exists($recording->storage_path)) {
            return back()->with('error', 'Berkas rekaman tidak ditemukan di penyimpanan.');
        }

        return $disk->response(
            $recording->storage_path,
            $recording->original_name ?? 'rekaman',
            ['Content-Type' => $recording->mime_type],
            'inline',
        );
    }

    /**
     * Archive a recording so the transcript is kept but the audio is hidden.
     */
    public function destroy(Patient $patient, Encounter $encounter, EncounterRecording $recording): RedirectResponse
    {
        if ($recording->encounter->status === EncounterStatus::Berjalan) {
            return back()->with('error', 'Rekaman tidak bisa diarsipkan selama kunjungan masih berjalan.');
        }

        $recording->delete();

        return back()->with('success', 'Rekaman diarsipkan.');
    }

    /**
     * Bring an archived recording back.
     */
    public function restore(Patient $patient, Encounter $encounter, EncounterRecording $recording): RedirectResponse
    {
        $recording->restore();

        return back()->with('success', 'Rekaman dikembalikan.');
    }
}
