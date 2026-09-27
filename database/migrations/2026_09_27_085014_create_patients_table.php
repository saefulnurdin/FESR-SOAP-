<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     *
     * Nilai enum ditulis sebagai literal, bukan dari App\Enums, agar skema ini
     * tetap sama persis meskipun enum di aplikasi berubah di kemudian hari.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table): void {
            $table->id();

            /*
             * Nomor rekam medis diberikan sistem setelah baris tersimpan, dengan
             * format MRN-<tahun>-<id>. Kolomnya nullable hanya selama proses
             * tersebut dan tetap unik, sehingga nomor tidak pernah dipakai ulang
             * walaupun pasien diarsipkan.
             */
            $table->string('medical_record_number')->nullable()->unique();

            $table->string('nik', 16)->nullable()->unique();
            $table->string('name');
            $table->enum('gender', ['L', 'P']);
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('phone', 30)->nullable();
            $table->enum('blood_type', ['A', 'B', 'AB', 'O', '-'])->nullable();
            $table->text('address')->nullable();
            $table->text('allergies')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
