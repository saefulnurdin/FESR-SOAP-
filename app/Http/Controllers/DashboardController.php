<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\Encounter;
use App\Models\Patient;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * Seluruh angka dikumpulkan di sini agar view tidak menyentuh basis data
     * secara langsung.
     */
    public function index(): View
    {
        return view('dashboard', [
            'patientCount' => Patient::query()->count(),
            'todayEncounterCount' => Encounter::occurredOn(today()->toDateString())->count(),
            'onGoingEncounterCount' => Encounter::onGoing()->count(),
            'documentTypeCount' => DocumentType::query()->count(),
        ]);
    }
}
