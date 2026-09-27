<?php

namespace App\Models;

use App\Enums\BloodType;
use App\Enums\Gender;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Data pasien. Nomor rekam medis dibuat sistem setelah baris tersimpan,
 * sedangkan NIK bersifat opsional karena tidak semua pasien memiliki NIK.
 */
#[Fillable(['nik', 'name', 'gender', 'birth_date', 'birth_place', 'phone', 'blood_type', 'address', 'allergies', 'notes'])]
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'birth_date' => 'date',
            'blood_type' => BloodType::class,
        ];
    }

    /**
     * Kunjungan pasien, dari yang terbaru.
     */
    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class)->latest('occurred_at');
    }

    /**
     * Umur pasien dalam tahun lengkap, atau null bila tanggal lahir kosong.
     */
    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn (): ?int => $this->birth_date?->age,
        );
    }

    /**
     * Nomor rekam medis diisi setelah id tersedia, sehingga nomornya selalu
     * unik tanpa perlu menghitung baris terakhir dan bebas dari race condition
     * antar dua permintaan yang datang bersamaan. Kolom ini tidak fillable,
     * jadi hanya model yang boleh mengisinya lewat forceFill.
     */
    protected static function booted(): void
    {
        static::created(function (self $patient): void {
            $patient->forceFill([
                'medical_record_number' => sprintf('MRN-%s-%06d', $patient->created_at->year, $patient->getKey()),
            ])->saveQuietly();
        });
    }
}
