<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas Aplikasi
    |--------------------------------------------------------------------------
    */

    'institution' => env('FESR_INSTITUTION', 'RS Contoh'),

    /*
    |--------------------------------------------------------------------------
    | Akun Administrator Awal
    |--------------------------------------------------------------------------
    |
    | Dipakai oleh AdminUserSeeder. Kredensial ini hanya berlaku pada
    | lingkungan pengembangan; ganti nilainya sebelum di-deploy.
    |
    */

    'seed' => [
        'admin_name' => env('ADMIN_NAME', 'Administrator'),

        'admin_email' => env('ADMIN_EMAIL', 'admin@rs-contoh.local'),

        'admin_password' => env('ADMIN_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Service (Python FastAPI)
    |--------------------------------------------------------------------------
    |
    | Berisi endpoint untuk Speech Recognition (Whisper) dan NLU. Nilai
    | diambil dari environment agar alamat service dapat berbeda antara
    | pengembangan, uji, dan deployment.
    |
    */

    'ai' => [
        'enabled' => (bool) env('AI_SERVICE_ENABLED', true),

        'base_url' => rtrim((string) env('AI_SERVICE_URL', 'http://127.0.0.1:8001'), '/'),

        /*
         * Transkripsi audio dapat memakan waktu lama pada model Whisper besar
         * dan CPU lambat, sehingga timeout dibuat jauh di atas batas bawaan.
         */
        'timeout' => (int) env('AI_SERVICE_TIMEOUT', 600),

        'language' => env('AI_SERVICE_LANGUAGE', 'id'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Penyimpanan Rekaman Audio
    |--------------------------------------------------------------------------
    */

    'audio' => [
        'disk' => env('AUDIO_DISK', 'local'),

        'path' => env('AUDIO_PATH', 'recordings'),

        'max_size_kb' => (int) env('AUDIO_MAX_SIZE_KB', 51200),

        /*
         * Batas durasi rekaman. Diperbarui oleh peramban karena server tidak
         * dapat memverifikasi panjang berkas tanpa ffmpeg.
         */
        'max_duration_seconds' => (int) env('AUDIO_MAX_DURATION_SECONDS', 600),

        /*
         * MediaRecorder di browser menghasilkan format berbeda tergantung
         * dukungan codec. Chrome/Edge menghasilkan webm, Firefox dapat
         * menghasilkan ogg, Safari dapat menghasilkan mp4.
         *
         * Yang dideteksi server adalah jenis MIME dari isi berkas, dan
         * berkas webm/ogg sering dilaporkan sebagai video/webm atau
         * application/ogg meski isinya hanya suara. Semua varian itu
         * diterima supaya rekaman dari peramban mana pun tidak tertolak.
         * Berkas acak yang tidak cocok salah satu jenis di bawah tetap
         * ditolak.
         */
        'allowed_mimes' => [
            'audio/webm',
            'video/webm',
            'video/x-matroska',
            'audio/ogg',
            'application/ogg',
            'audio/mp4',
            'video/mp4',
            'audio/mpeg',
            'audio/wav',
            'audio/x-wav',
        ],
    ],

];
