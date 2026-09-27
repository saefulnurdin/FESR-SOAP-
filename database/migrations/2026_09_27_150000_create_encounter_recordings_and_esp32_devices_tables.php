<?php

use App\Enums\RecordingSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * encounter_recordings menyimpan rekaman dan transkrip yang menempel pada
     * satu kunjungan. Berkasnya tidak disimpan di dalam database, melainkan di
     * disk privat; kolom di sini hanya menyimpan lokasi dan property-nya.
     *
     * esp32_devices menyimpan identitas perangkat perekam. Yang disimpan
     * hanyalah hash token, bukan tokennya sendiri, sehingga bocornya isi
     * tabel ini tidak cukup untuk dipakai mengunggah.
     *
     * softDeletes dipakai supaya rekaman hanya diarsipkan: transkrip yang
     * sudah diisi tidak hilang karena berkas diarsipkan.
     */
    public function up(): void
    {
        Schema::create('encounter_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 20)->default(RecordingSource::Browser->value);
            $table->string('mime_type', 60);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('storage_disk', 30)->default('local');
            $table->string('storage_path');
            $table->string('original_name')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->text('transcript')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['encounter_id', 'created_at']);
            $table->index(['source', 'created_at']);
        });

        Schema::create('esp32_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('esp32_devices');
        Schema::dropIfExists('encounter_recordings');
    }
};
