<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manajemen pasien. Semua akun yang aktif dapat mengelola data pasien; akun
 * nonaktif tidak pernah sampai ke sini karena middleware EnsureUserIsActive
 * telah mengakhiri sesinya.
 */
class PatientController extends Controller
{
    /**
     * Display a listing of the patients.
     */
    public function index(Request $request): View
    {
        $archived = $request->boolean('archived');

        $patients = Patient::query()
            ->withCount('encounters')
            ->when($archived, fn (Builder $query) => $query->onlyTrashed())
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = trim((string) $request->string('search'));

                $query->where(fn (Builder $searchQuery) => $searchQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('medical_record_number', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                );
            })
            ->when($request->filled('gender'), fn (Builder $query) => $query->where('gender', $request->string('gender')->value()))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', [
            'patients' => $patients,
            'archived' => $archived,
        ]);
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create(): View
    {
        return view('patients.create');
    }

    /**
     * Store a newly created patient.
     */
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $patient = Patient::create($request->validated());

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', "Pasien {$patient->name} berhasil didaftarkan dengan nomor rekam medis {$patient->medical_record_number}.");
    }

    /**
     * Display the given patient with the encounter history.
     */
    public function show(Patient $patient): View
    {
        $patient->load(['encounters' => fn ($query) => $query->with('doctor')->latest('occurred_at')]);

        return view('patients.show', [
            'patient' => $patient,
        ]);
    }

    /**
     * Show the form for editing the given patient.
     */
    public function edit(Patient $patient): View
    {
        return view('patients.edit', [
            'patient' => $patient,
        ]);
    }

    /**
     * Update the given patient.
     */
    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $patient->update($request->validated());

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', "Data pasien {$patient->name} berhasil diperbarui.");
    }

    /**
     * Archive the given patient.
     *
     * Data tidak dihapus permanen karena menjadi bagian dari riwayat medis;
     * arsip dapat dipulihkan kembali dari halaman arsip.
     */
    public function destroy(Patient $patient): RedirectResponse
    {
        $patient->delete();

        return redirect()
            ->route('patients.index')
            ->with('success', "Pasien {$patient->name} telah diarsipkan. Data tetap tersimpan dan dapat dipulihkan.");
    }

    /**
     * Restore the given patient from the archive.
     */
    public function restore(Patient $patient): RedirectResponse
    {
        $patient->restore();

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', "Pasien {$patient->name} berhasil dipulihkan dari arsip.");
    }
}
