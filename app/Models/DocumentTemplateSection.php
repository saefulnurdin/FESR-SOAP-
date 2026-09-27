<?php

namespace App\Models;

use Database\Factories\DocumentTemplateSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Satu bagian dalam template dokumen, misalnya bagian Subjective pada catatan
 * SOAP. Key bagian dipakai sebagai penanda saat memetakan hasil ekstraksi NLU
 * sehingga nilainya tetap walaupun judulnya diubah.
 */
#[Fillable(['document_template_id', 'key', 'title', 'hint', 'sort_order'])]
class DocumentTemplateSection extends Model
{
    /** @use HasFactory<DocumentTemplateSectionFactory> */
    use HasFactory;

    /**
     * Template pemilik bagian.
     *
     * @return BelongsTo<DocumentTemplate, $this>
     */
    public function documentTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    /**
     * Isian dalam bagian ini sesuai urutan tampil.
     *
     * @return HasMany<DocumentTemplateField, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(DocumentTemplateField::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
