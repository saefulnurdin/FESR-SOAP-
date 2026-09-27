<?php

namespace App\Models;

use Database\Factories\DocumentTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Katalog jenis dokumen yang dikenali fasilitas kesehatan, misalnya catatan
 * SOAP. Jenis dokumen hanya mengelompokkan template; isi dan struktur dokumen
 * ditentukan oleh template.
 */
#[Fillable(['code', 'name', 'description', 'is_active'])]
class DocumentType extends Model
{
    /** @use HasFactory<DocumentTypeFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Seluruh template milik jenis dokumen ini.
     *
     * @return HasMany<DocumentTemplate, $this>
     */
    public function templates(): HasMany
    {
        return $this->hasMany(DocumentTemplate::class);
    }

    /**
     * Template yang masih boleh dipakai untuk membuat dokumen.
     *
     * @return HasMany<DocumentTemplate, $this>
     */
    public function activeTemplates(): HasMany
    {
        return $this->templates()->active();
    }
}
