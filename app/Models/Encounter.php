<?php

namespace App\Models;

use App\Enums\EncounterStatus;
use App\Enums\VisitType;
use Database\Factories\EncounterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Satu kunjungan pasien ke fasilitas kesehatan. Rekaman audio dan draf SOAP
 * pada fase berikutnya akan dilampirkan pada model ini.
 */
#[Fillable(['doctor_id', 'occurred_at', 'visit_type', 'status', 'chief_complaint', 'notes'])]
class Encounter extends Model
{
    /** @use HasFactory<EncounterFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Nilai bawaan agar kunjungan yang baru dibuat langsung berstatus berjalan
     * dan konsisten dengan default pada kolom di basis data.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => EncounterStatus::Berjalan->value,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'visit_type' => VisitType::class,
            'status' => EncounterStatus::class,
        ];
    }

    /**
     * Pasien yang melakukan kunjungan ini.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Petugas yang menangani kunjungan, bila akunnya masih ada.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Kunjungan yang masih dapat diisi rekaman dan draf SOAP.
     */
    #[Scope]
    protected function onGoing(Builder $query): Builder
    {
        return $query->where('status', EncounterStatus::Berjalan);
    }

    /**
     * Kunjungan yang terjadi pada tanggal tertentu.
     */
    #[Scope]
    protected function occurredOn(Builder $query, string $date): Builder
    {
        return $query->whereDate('occurred_at', $date);
    }
}
