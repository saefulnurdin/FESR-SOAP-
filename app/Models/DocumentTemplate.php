<?php

namespace App\Models;

use Database\Factories\DocumentTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Template dokumen: definisi bagian dan isian yang harus diisi saat dokumen
 * dibuat. Template inilah yang menjadi target pemetaan hasil ekstraksi NLU.
 */
#[Fillable(['document_type_id', 'name', 'description', 'is_active'])]
class DocumentTemplate extends Model
{
    /** @use HasFactory<DocumentTemplateFactory> */
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
     * Jenis dokumen pemilik template.
     *
     * @return BelongsTo<DocumentType, $this>
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Bagian template sesuai urutan tampil.
     *
     * @return HasMany<DocumentTemplateSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(DocumentTemplateSection::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Seluruh isian pada seluruh bagian template ini.
     *
     * Selain dipakai untuk membaca struktur, relasi ini memastikan field yang
     * dimaksud benar-benar milik template pada route bersarang.
     *
     * @return HasManyThrough<DocumentTemplateField, DocumentTemplateSection, $this>
     */
    public function fields(): HasManyThrough
    {
        return $this->hasManyThrough(
            DocumentTemplateField::class,
            DocumentTemplateSection::class,
            'document_template_id',
            'document_template_section_id',
        );
    }

    /**
     * Template yang masih boleh dipakai untuk membuat dokumen.
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Apakah template sudah memiliki bagian yang dapat diisi.
     */
    public function hasStructure(): bool
    {
        return $this->relationLoaded('sections')
            ? $this->sections->isNotEmpty()
            : $this->sections()->exists();
    }
}
