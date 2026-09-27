<?php

use App\Http\Controllers\EncounterController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

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
});

require __DIR__.'/auth.php';
