<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\DocumentTemplateFieldController;
use App\Http\Controllers\DocumentTemplateSectionController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\EncounterController;
use App\Http\Controllers\EncounterRecordingController;
use App\Http\Controllers\Esp32DeviceController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    /*
     * Data pasien dapat dikelola seluruh petugas yang aktif, sehingga tidak
     * memakai gate seperti halaman pengguna.
     */
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index');
        Route::get('/create', [PatientController::class, 'create'])->name('create');
        Route::post('/', [PatientController::class, 'store'])->name('store');
        Route::get('/{patient}', [PatientController::class, 'show'])->name('show');

        /*
         * Pemulihan memerlukan akses ke baris yang sudah diarsipkan, yang
         * tidak lagi ditemukan oleh route model binding bawaan.
         */
        Route::post('/{patient}/restore', [PatientController::class, 'restore'])
            ->withTrashed()
            ->name('restore');

        Route::get('/{patient}/edit', [PatientController::class, 'edit'])->name('edit');
        Route::put('/{patient}', [PatientController::class, 'update'])->name('update');
        Route::delete('/{patient}', [PatientController::class, 'destroy'])->name('destroy');

        Route::prefix('{patient}/encounters')->name('encounters.')->scopeBindings()->group(function () {
            Route::get('/create', [EncounterController::class, 'create'])->name('create');
            Route::post('/', [EncounterController::class, 'store'])->name('store');
            Route::get('/{encounter}/edit', [EncounterController::class, 'edit'])->name('edit');
            Route::put('/{encounter}', [EncounterController::class, 'update'])->name('update');
            Route::delete('/{encounter}', [EncounterController::class, 'destroy'])->name('destroy');

            /*
             * Rekaman memakai nama route sendiri, bukan resource, karena
             * pemutarannya berupa aliran byte dan bukan halaman CRUD biasa.
             * Gate manage-recordings di route di bawah memakai parameter route
             * agar pemeriksaan dilakukan per rekaman.
             */
            Route::prefix('{encounter}/recordings')
                ->name('recordings.')
                ->middleware('can:record-encounter,encounter')
                ->group(function () {
                    Route::get('/', [EncounterRecordingController::class, 'index'])->name('index');
                    Route::post('/', [EncounterRecordingController::class, 'store'])->name('store');
                });

            Route::patch('{encounter}/recordings/{recording}/transcript', [EncounterRecordingController::class, 'updateTranscript'])
                ->middleware('can:manage-recordings,recording')
                ->name('recordings.transcript.update');

            Route::get('{encounter}/recordings/{recording}/audio', [EncounterRecordingController::class, 'audio'])
                ->middleware('can:manage-recordings,recording')
                ->name('recordings.audio');

            Route::delete('{encounter}/recordings/{recording}', [EncounterRecordingController::class, 'destroy'])
                ->middleware('can:manage-recordings,recording')
                ->name('recordings.destroy');

            Route::post('{encounter}/recordings/{recording}/restore', [EncounterRecordingController::class, 'restore'])
                ->withTrashed()
                ->middleware('can:manage-recordings,recording')
                ->name('recordings.restore');
        });
    });

    Route::middleware('can:manage-users')->prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    /*
     * Struktur dokumen menentukan ke mana hasil ekstraksi NLU dipetakan, jadi
     * hanya administrator yang boleh mengubahnya.(scopeBindings) menjaga agar
     * bagian dan isian yang dimaksud benar-benar milik template pada route.
     */
    Route::middleware('can:manage-document-templates')->prefix('admin')->name('admin.')->scopeBindings()->group(function () {
        Route::resource('document-types', DocumentTypeController::class)
            ->except(['show'])
            ->parameters(['document-types' => 'documentType']);

        Route::prefix('document-types/{documentType}/templates')->name('document-types.templates.')->group(function () {
            Route::get('/', [DocumentTemplateController::class, 'index'])->name('index');
            Route::get('/create', [DocumentTemplateController::class, 'create'])->name('create');
            Route::post('/', [DocumentTemplateController::class, 'store'])->name('store');
        });

        Route::prefix('templates/{template}')->name('templates.')->group(function () {
            Route::get('/edit', [DocumentTemplateController::class, 'edit'])->name('edit');
            Route::put('/', [DocumentTemplateController::class, 'update'])->name('update');
            Route::delete('/', [DocumentTemplateController::class, 'destroy'])->name('destroy');

            Route::post('/sections', [DocumentTemplateSectionController::class, 'store'])->name('sections.store');
            Route::delete('/sections/{section}', [DocumentTemplateSectionController::class, 'destroy'])->name('sections.destroy');

            Route::get('/fields/create', [DocumentTemplateFieldController::class, 'create'])->name('fields.create');
            Route::post('/fields', [DocumentTemplateFieldController::class, 'store'])->name('fields.store');
            Route::get('/fields/{field}/edit', [DocumentTemplateFieldController::class, 'edit'])->name('fields.edit');
            Route::put('/fields/{field}', [DocumentTemplateFieldController::class, 'update'])->name('fields.update');
            Route::delete('/fields/{field}', [DocumentTemplateFieldController::class, 'destroy'])->name('fields.destroy');
        });
    });

    /*
     * Perangkat perekam dikelola terpisah dari template dokumen, karena
     * keduanya tidak berkaitan: yang boleh menerbitkan token adalah admin,
     * dan token itu sendiri yang menentukan siapa pemilik rekamannya.
     */
    Route::middleware('can:manage-recording-devices')->prefix('admin/devices')->name('admin.devices.')->group(function () {
        Route::get('/', [Esp32DeviceController::class, 'index'])->name('index');
        Route::post('/', [Esp32DeviceController::class, 'store'])->name('store');
        Route::post('/{esp32Device}/toggle', [Esp32DeviceController::class, 'toggle'])->name('toggle');
        Route::post('/{esp32Device}/token', [Esp32DeviceController::class, 'regenerateToken'])->name('token.store');
        Route::delete('/{esp32Device}', [Esp32DeviceController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
