# FESR — Front-End Speech Recognition untuk Dokumentasi Klinik

Prototipe aplikasi web untuk membantu tenaga medis mendokumentasikan kondisi
pasien dengan suara. Rekaman suara diproses menjadi draf dokumen klinis
(bahan utama penelitian: **SOAP Note**) yang kemudian ditinjau dan disetujui
tenaga medis.

## Arsitektur

```
Browser  ──REST/JSON──▶  Laravel  ──HTTP/JSON──▶  AI Service (FastAPI)
(MediaRecorder,             │                        │
 Blade, Tailwind,           │                        ├─ Whisper (Speech Recognition)
 Alpine.js)                 │                        └─ NLU (spaCy + rule-based)
                            ▼
                          MySQL
```

| Bagian            | Teknologi                                    |
| ----------------- | -------------------------------------------- |
| Web aplikasi      | Laravel, PHP 8.5, Blade, Tailwind v4, Alpine.js, Vite |
| Database          | MySQL 8, Eloquent ORM, migrations, seeders   |
| AI Service        | Python 3.14, FastAPI, Uvicorn                |
| Speech Recognition| Whisper (`faster-whisper`, dijalankan lokal) |
| NLU               | spaCy + rule-based, scikit-learn             |
| Audio browser     | MediaRecorder API, Web Audio API             |
| Komunikasi        | REST API, JSON                               |

Seluruh proses AI berjalan **lokal**. Tidak ada API AI berbayar yang dipakai.

## Struktur Direktori

```
fesr-soap/
├── app/
│   ├── Http/Controllers/Auth/   Login, logout, lupa & atur ulang kata sandi
│   ├── Http/Controllers/UserController.php  Manajemen pengguna (administrator)
│   ├── Http/Middleware/         EnsureUserIsActive
│   └── Models/User.php
├── config/fesr.php         Konfigurasi AI Service, penyimpanan audio, akun admin awal
├── database/               Migrasi, factory, seeder
├── resources/
│   ├── css/app.css         Entry point Tailwind v4
│   ├── js/app.js           Entry point Alpine.js
│   └── views/              Blade: layouts, components, auth, profile, admin/users
├── routes/web.php          Route web
│   └── routes/auth.php     Route autentikasi
├── tests/                  PHPUnit (Unit & Feature)
└── ai-service/             Python FastAPI
    ├── app/
    │   ├── main.py         Pembuat aplikasi FastAPI
    │   ├── config.py       Settings (prefix FESR_AI_)
    │   ├── routers/        Endpoint
    │   └── schemas/        Skema Pydantic
    ├── tests/              Pytest
    ├── requirements.txt
    └── requirements-dev.txt
```

## Prasyarat

| Komponen            | Versi    | Catatan                                     |
| ------------------- | -------- | ------------------------------------------- |
| PHP                 | 8.2+     | Ekstensi `pdo_mysql`, `mbstring`, `curl`    |
| Composer            | 2.x      |                                             |
| Node.js             | 20+      |                                             |
| MySQL               | 8.0+     | Database `fesr` harus sudah dibuat          |
| Python              | 3.11+    | Diuji pada 3.14                             |

## Instalasi

### 1. Aplikasi Laravel

```bash
composer install
npm install
```

Salin `.env.example` menjadi `.env`, lalu sesuaikan koneksi database:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fesr
DB_USERNAME=root
DB_PASSWORD=
```

Buat database bila belum ada:

```sql
CREATE DATABASE fesr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Lalu jalankan:

```bash
php artisan key:generate
php artisan migrate --seed
npm run build
```

`--seed` membuat satu akun administrator awal. Kredensialnya diatur lewat
environment berikut (lihat `.env.example`):

```dotenv
ADMIN_NAME="Administrator"
ADMIN_EMAIL=admin@rs-contoh.local
ADMIN_PASSWORD=password
```

Akun hanya dapat dibuat oleh administrator dari halaman **Pengguna**; tidak
ada pendaftaran mandiri. Halaman tersebut dapat diakses oleh akun dengan
`users.is_admin` bernilai benar.

### 2. AI Service (Python)

```bash
cd ai-service
py -m venv .venv
.venv/Scripts/python -m pip install -r requirements-dev.txt
copy .env.example .env
```

## Menjalankan Aplikasi

Butuh dua terminal.

```bash
# Terminal 1 — AI Service
cd ai-service
.venv/Scripts/python -m uvicorn app.main:app --reload --port 8001
```

```bash
# Terminal 2 — Laravel
php artisan serve
```

Buka `http://localhost:8000`, lalu masuk dengan akun administrator hasil seeder.
Dokumentasi OpenAPI AI Service tersedia di `http://localhost:8001/docs`, health
check di `http://localhost:8001/health`.

Untuk pengembangan frontend dengan hot reload, jalankan `npm run dev` pada
terminal ketiga. Assets hasil produksi diambil dari `npm run build`.

Tautan pengaturan ulang kata sandi dikirim lewat mailer. Pada `.env`
pengembangan `MAIL_MAILER=log` sehingga tautannya dapat dibaca dari
`storage/logs/laravel.log`.

## Menjalankan Pengujian

```bash
# Laravel
php artisan test

# AI Service
cd ai-service
.venv/Scripts/python -m pytest
```

Pengujian Laravel memakai SQLite in-memory sehingga tidak menyentuh database
MySQL pengembangan. Pengujian yang memerlukan MySQL dapat dijalankan dengan
`DB_CONNECTION=mysql php artisan test`.

## Tahap Pengembangan

| Fase | Cakupan                                          | Status |
| ---- | ------------------------------------------------ | ------ |
| 0    | Fondasi: Laravel, MySQL, Tailwind, Alpine, FastAPI | Selesai |
| 1    | Autentikasi & manajemen pengguna                  | Selesai |
| 2    | Manajemen pasien & encounter                      | Berikutnya |
| 3    | Document type & document template (seed SOAP)     | -      |
| 4    | Rekaman audio & transkrip                         | -      |
| 5    | Speech recognition dengan Whisper                 | -      |
| 6    | NLU & ekstraksi entitas klinis                    | -      |
| 7    | SOAP mapper, review, penyimpanan dokumen          | -      |
| 8    | Audit trail, pengujian, dokumentasi skripsi       | -      |
