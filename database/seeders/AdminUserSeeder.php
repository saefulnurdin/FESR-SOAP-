<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun administrator awal. Kredensial diambil dari config agar dapat diubah
 * melalui environment, bukan ditulis langsung di dalam kode.
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => config('fesr.seed.admin_email')],
            [
                'name' => config('fesr.seed.admin_name'),
                'password' => config('fesr.seed.admin_password'),
                'is_admin' => true,
                'is_active' => true,
            ],
        );
    }
}
