<?php

use App\Http\Controllers\Api\DeviceRecordingController;
use App\Http\Middleware\AuthenticateDevice;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute API
|--------------------------------------------------------------------------
|
| Rute di sini hanya untuk perangkat perekam ESP32, bukan untuk aplikasi
| peramban. Autentikasinya memakai token perangkat pada header
| X-Device-Token, bukan sesi, sehingga tidak ada yang perlu disimpan di
| peramban.
|
*/

Route::middleware(AuthenticateDevice::class)->group(function (): void {
    Route::get('device', [DeviceRecordingController::class, 'show'])->name('api.device.show');

    Route::post('encounters/{encounter}/recordings', [DeviceRecordingController::class, 'store'])
        ->name('api.encounters.recordings.store');
});
