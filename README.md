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
│   ├── Enums/               Gender, BloodType, VisitType, EncounterStatus,
│   │                        DocumentFieldType
│   ├── Http/Controllers/Auth/   Login, logout, lupa & atur ulang kata sandi
│   ├── Http/Controllers/UserController.php  Manajemen pengguna (administrator)
│   ├── Http/Controllers/PatientController.php  Manajemen pasien
│   ├── Http/Controllers/EncounterController.php  Kunjungan pasien
│   ├── Http/Controllers/DocumentTypeController.php  Katalog jenis dokumen
│   ├── Http/Controllers/DocumentTemplateController.php  Template dokumen
│   ├── Http/Controllers/DocumentTemplateSectionController.php  Bagian template
│   ├── Http/Controllers/DocumentTemplateFieldController.php  Isian template
│   ├── Http/Middleware/         EnsureUserIsActive
│   └── Models/                  User, Patient, Encounter, DocumentType,
│                                DocumentTemplate, DocumentTemplateSection,
│                                DocumentTemplateField
├── config/fesr.php         Konfigurasi AI Service, penyimpanan audio, akun admin awal
├── database/               Migrasi, factory, seeder
├── resources/
│   ├── css/app.css         Entry point Tailwind v4
│   ├── js/app.js           Entry point Alpine.js
│   └── views/              Blade: layouts, components, auth, profile, admin/users,
│                           admin/document-types, admin/templates, patients,
│                           encounters
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

## Data Pasien

- Nomor rekam medis (`MRN-<tahun>-<nomor urut>`) dibuat otomatis oleh sistem
  setelah pasien terdaftar dan tidak dapat diubah.
- NIK bersifat opsional tetapi unik bila diisi, sehingga aman untuk pasien tanpa
  NIK.
- Data pasien dikelola seluruh petugas yang aktif; akun nonaktif tidak dapat
  mengaksesnya.
- Menghapus pasien berarti mengarsipkan, bukan menghapus permanen. Riwayat
  kunjungan tetap tersimpan dan pasien dapat dipulihkan dari tab **Arsip**.

Setiap kunjungan (encounter) mencatat waktu, jenis kunjungan, keluhan utama,
status, serta petugas yang menanganinya. Rekaman audio dan draf SOAP dilampirkan
pada kunjungan ini.

### Rekaman Audio

Setiap rekaman menempel pada satu kunjungan dan disimpan di `encounter_recordings`.
Berkasnya berada pada disk privat (`storage/app/private/recordings/`) dan tidak
punya URL publik, karena rekapan kondisi pasien hanya boleh didengarkan oleh
petugas yang menangani kunjungan tersebut dan administrator.

- **Perekaman di peramban** memakai `MediaRecorder` beserta `AnalyserNode` untuk
  indikator level suara, dengan batas 10 menit dan penghentian otomatis.
- **Unggahan dari perangkat ESP32** memakai endpoint yang sama, tetapi
  diautentikasi dengan token perangkat pada header `X-Device-Token`.
- Rekaman baru hanya bisa ditambahkan pada kunjungan berstatus **berjalan**.
- Rekaman tidak bisa diarsipkan selama kunjungan masih berjalan; setelah diarsipkan
  transkripnya tetap tersimpan dan rekaman dapat dikembalikan.
- Transkrip diisi manual pada fase ini. Pengenalan suara otomatis menyusul.
- Audio disimpan apa adanya sesuai format yang dikirim pengirim, tanpa
  konversi di server, karena server tidak memiliki ffmpeg. Panjang rekaman
  diverifikasi oleh peramban dan perangkat, bukan server.

Token perangkat diterbitkan dari halaman **Perangkat perekam** dan hanya
ditampilkan satu kali; yang disimpan di database adalah hash-nya.

### Struktur Dokumen

Dokumen disusun dari empat tabel: `document_types` (katalog jenis dokumen),
`document_templates`, `document_template_sections`, dan
`document_template_fields`. Struktur inilah yang menjadi target pemetaan hasil
ekstraksi, sehingga tiap bagian dan isian memiliki **key** yang stabil:

- **Key** ditulis dengan huruf kecil, angka, dan garis bawah
  (`tekanan_darah`). Nilainya tidak ikut berubah ketika judul diganti, sehingga
  rekaman lama tetap dapat dibaca.
- Setiap isian menyimpan **satuan** terpisah dari label, supaya hasil
  ekstraksi dapat dicocokkan dengan satuan yang benar.
- Tipe isian: teks pendek, teks panjang, angka, pilihan, ya/tidak, dan
  tanggal. Tipe **pilihan** wajib menyertakan daftar pilihan.
- **Petunjuk pengisian** pada setiap bagian ikut dibaca layanan AI untuk
  memahami apa yang perlu diambil dari rekaman.

Template dapat dinonaktifkan tanpa menghapus strukturnya, dan jenis dokumen
yang masih memiliki template tidak dapat dihapus agar struktur yang sudah
disiapkan tidak hilang karena salah klik. Seluruh pengelolaan template hanya
dapat dilakukan administrator.

Seeder `SoapTemplateSeeder` menyiapkan satu jenis dokumen `soap` dengan template
**SOAP Dewasa**: bagian Subjective, Objective, Assessment, dan Plan, disertai
14 isian. Menjalankan seeder berkali-kali tidak menggandakan data.

```bash
php artisan db:seed --class=SoapTemplateSeeder
```

### 3. AI Service (Python)

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
| 2    | Manajemen pasien & encounter                      | Selesai |
| 3    | Document type & document template (seed SOAP)     | Selesai |
| 4    | Rekaman audio & transkrip                         | Selesai |
| 5    | Speech recognition dengan Whisper                 | Berikutnya |
| 6    | NLU & ekstraksi entitas klinis                    | -      |
| 7    | SOAP mapper, review, penyimpanan dokumen          | -      |
| 8    | Audit trail, pengujian, dokumentasi skripsi       | -      |
