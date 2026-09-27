<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveEncounterRequest;
use App\Models\Encounter;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Kunjungan pasien. Setiap kunjungan selalu dikaitkan dengan satu pasien dan
 * dicatat oleh petugas yang sedang masuk ke aplikasi.
 */
class EncounterController extends Controller
{
    /**
     * Show the form for creating a new encounter.
     */
    public function create(Patient $patient): View
    {
        return view('encounters.create', [
            'patient' => $patient,
        ]);
    }

    /**
     * Store a newly created encounter.
     */
    public function store(SaveEncounterRequest $request, Patient $patient): RedirectResponse
    {
        $encounter = $patient->encounters()->create([
            ...$request->validated(),

            /*
             * Petugas diambil dari akun yang sedang masuk, bukan dari isian
             * form, sehingga riwayat kunjungan selalu dapat ditelusuri ke
             * akun yang benar.
             */
            'doctor_id' => $request->user()->getKey(),
        ]);

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', "Kunjungan {$encounter->visit_type->label()} atas nama {$patient->name} berhasil dicatat.");
    }

    /**
     * Show the form for editing the given encounter.
     */
    public function edit(Patient $patient, Encounter $encounter): View
    {
        return view('encounters.edit', [
            'patient' => $patient,
            'encounter' => $encounter,
        ]);
    }

    /**
     * Update the given encounter.
     */
    public function update(SaveEncounterRequest $request, Patient $patient, Encounter $encounter): RedirectResponse
    {
        $encounter->update($request->validated());

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', "Kunjungan tanggal {$encounter->occurred_at->format('d/m/Y H:i')} berhasil diperbarui.");
    }

    /**
     * Archive the given encounter.
     */
    public function destroy(Patient $patient, Encounter $encounter): RedirectResponse
    {
        $encounter->delete();

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', 'Kunjungan telah diarsipkan.');
    }
}
