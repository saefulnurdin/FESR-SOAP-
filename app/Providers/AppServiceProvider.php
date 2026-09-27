<?php

namespace App\Providers;

use App\Models\Encounter;
use App\Models\EncounterRecording;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', fn (User $user): bool => $user->is_admin);

        Gate::define('manage-document-templates', fn (User $user): bool => $user->is_admin);

        Gate::define('manage-recording-devices', fn (User $user): bool => $user->is_admin);

        /*
         * Rekaman kondisi pasien tidak untuk semua petugas, hanya untuk yang
         * menangani kunjungan tersebut. Administrator tetap boleh supaya
         * pemeriksaan dan penyesuaian data tetap mungkin.
         */
        Gate::define('manage-recordings', function (User $user, EncounterRecording $recording): bool {
            return $user->is_admin || $recording->encounter->doctor_id === $user->getKey();
        });

        Gate::define('record-encounter', function (User $user, Encounter $encounter): bool {
            return $user->is_admin || $encounter->doctor_id === $user->getKey();
        });
    }
}
