<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     *
     * Kunjungan (encounter) adalah container rekaman audio dan draf SOAP pada
     * fase berikutnya, sehingga satu pasien dapat memiliki banyak kunjungan.
     * Nilai enum ditulis sebagai literal agar skema ini tidak ikut berubah
     * apabila enum di aplikasi berkembang.
     */
    public function up(): void
    {
        Schema::create('encounters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();

            /*
             * Petugas yang menangani boleh kosong, misalnya pasien lama yang
             * didaftarkan sebelum fitur ini ada. Bila akun petugas dihapus,
             * kunjungan tidak ikut terhapus dan kolomnya dikosongkan.
             */
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('occurred_at');
            $table->enum('visit_type', ['rawat_jalan', 'gawat_darurat', 'rawat_inap', 'telekonsultasi']);
            $table->enum('status', ['berjalan', 'selesai', 'dibatalkan'])->default('berjalan');
            $table->text('chief_complaint')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            /*
             * Daftar riwayat kunjungan selalu mengambil satu pasien lalu
             * diurutkan berdasarkan waktu kunjungan.
             */
            $table->index(['patient_id', 'occurred_at']);
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};
