<?php

namespace App\Http\Controllers\Api;

use App\Enums\EncounterStatus;
use App\Enums\RecordingSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecordingRequest;
use App\Models\Encounter;
use App\Models\Esp32Device;
use App\Services\RecordingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint untuk perangkat perekam ESP32.
 *
 * Hanya menerima permintaan yang memakai X-Device-Token dan kunjungan yang
 * sedang berjalan. Pengiriman memakai endpoint yang sama dengan peramban
 * supaya bentuk datanya tidak berbeda.
 */
class DeviceRecordingController extends Controller
{
    /**
     * Confirm that the device is recognised and report its owner.
     */
    public function show(Request $request): JsonResponse
    {
        $device = $this->device($request);

        return response()->json([
            'device' => $device->only(['id', 'name', 'last_seen_at']),
            'owner' => $device->user->only(['id', 'name', 'is_admin']),
        ]);
    }

    /**
     * Store a recording sent by a device.
     */
    public function store(StoreRecordingRequest $request, Encounter $encounter, RecordingService $recordings): JsonResponse
    {
        $device = $this->device($request);

        /*
         * Perangkat boleh merekam kunjungan milik pemiliknya sendiri. Admin
         * tetap diizinkan supaya pengujian tidak perluberganti akun.
         */
        abort_unless(
            $device->user->is_admin || $encounter->doctor_id === $device->user->getKey(),
            403,
            'Kunjungan ini bukan milik pemilik perangkat.',
        );

        if ($encounter->status !== EncounterStatus::Berjalan) {
            return response()->json([
                'message' => 'Kunjungan ini sudah tidak berjalan.',
            ], 409);
        }

        $recording = $recordings->store(
            encounter: $encounter,
            audio: $request->file('audio'),
            recordedBy: $device->user,
            source: RecordingSource::Esp32,
            durationSeconds: $request->integer('duration_seconds') ?: null,
            capturedAt: $request->input('captured_at'),
        );

        return response()->json([
            'message' => 'Rekaman tersimpan.',
            'recording_id' => $recording->getKey(),
            'size_bytes' => $recording->size_bytes,
        ], 201);
    }

    /**
     * The device that sent the request.
     */
    private function device(Request $request): Esp32Device
    {
        return $request->attributes->get('device');
    }
}
