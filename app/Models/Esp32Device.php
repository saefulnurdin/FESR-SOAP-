<?php

namespace App\Models;

use Database\Factories\Esp32DeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Perangkat perekam yang dilatih dan dapat mengirim rekaman ke aplikasi tanpa
 * peramban. Token disimpan sebagai hash; nilai aslinya hanya ditampilkan
 * sekali ketika perangkat dibuat.
 */
#[Fillable(['name', 'user_id', 'token_hash', 'last_seen_at', 'is_active'])]
class Esp32Device extends Model
{
    /** @use HasFactory<Esp32DeviceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Petugas pemilik perangkat. Rekamannya tetap tercatat atas nama orang
     * ini walaupun perangkat yang mengirim.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Whether the device has reported anything recently.
     */
    public function wasSeenRecently(): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->isAfter(now()->subMinutes(15));
    }
}
