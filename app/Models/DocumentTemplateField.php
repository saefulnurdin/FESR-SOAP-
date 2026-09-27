<?php

namespace App\Models;

use App\Enums\DocumentFieldType;
use Database\Factories\DocumentTemplateFieldFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu isian dalam bagian template. Field dengan tipe Pilihan harus
 * menyertakan daftar opsi; field lain mengabaikan kolom tersebut.
 */
#[Fillable(['document_template_section_id', 'key', 'label', 'type', 'options', 'unit', 'is_required', 'sort_order'])]
class DocumentTemplateField extends Model
{
    /** @use HasFactory<DocumentTemplateFieldFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DocumentFieldType::class,
            'options' => 'array',
            'is_required' => 'boolean',
        ];
    }

    /**
     * Bagian pemilik isian.
     *
     * @return BelongsTo<DocumentTemplateSection, $this>
     */
    public function documentTemplateSection(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplateSection::class);
    }

    /**
     * Nama field lengkap dengan satuan, untuk ditampilkan di antarmuka.
     */
    public function labelWithUnit(): string
    {
        return $this->unit === null ? $this->label : "{$this->label} ({$this->unit})";
    }
}
