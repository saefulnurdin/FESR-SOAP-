<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveDocumentTemplateFieldRequest;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateField;
use App\Models\DocumentTemplateSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentTemplateFieldController extends Controller
{
    /**
     * Show the form for adding a field to a section of a template.
     *
     * Bagian tujuan dapat dipilih lebih awal lewat query string, misalnya
     * ?section=3 dari tombol tambah isian pada salah satu bagian.
     */
    public function create(Request $request, DocumentTemplate $template): View
    {
        $template->load('documentType');

        $sections = $template->sections()->get();

        return view('admin.templates.fields.create', [
            'template' => $template,
            'sections' => $sections,
            'section' => $sections->firstWhere('id', $request->integer('section')),
        ]);
    }

    /**
     * Store a newly created field in storage.
     */
    public function store(SaveDocumentTemplateFieldRequest $request, DocumentTemplate $template): RedirectResponse
    {
        $section = $this->sectionFromRequest($request, $template);

        $section->fields()->create([
            ...$request->safe()->only(['key', 'label', 'type', 'options', 'unit', 'is_required']),
            'sort_order' => $this->nextSortOrder($section),
        ]);

        return to_route('admin.templates.edit', $template)
            ->with('success', 'Isian berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified field.
     */
    public function edit(DocumentTemplate $template, DocumentTemplateField $field): View
    {
        $template->load('documentType');
        $field->load('documentTemplateSection');

        return view('admin.templates.fields.edit', [
            'template' => $template,
            'sections' => $template->sections()->get(),
            'section' => $field->documentTemplateSection,
            'field' => $field,
        ]);
    }

    /**
     * Update the specified field in storage.
     */
    public function update(SaveDocumentTemplateFieldRequest $request, DocumentTemplate $template, DocumentTemplateField $field): RedirectResponse
    {
        $section = $this->sectionFromRequest($request, $template);
        $movedToAnotherSection = $section->isNot($field->documentTemplateSection);

        $field->update([
            ...$request->safe()->only(['document_template_section_id', 'key', 'label', 'type', 'options', 'unit', 'is_required']),
            'sort_order' => $movedToAnotherSection
                ? $this->nextSortOrder($section, $field)
                : $field->sort_order,
        ]);

        return to_route('admin.templates.edit', $template)
            ->with('success', 'Isian berhasil diperbarui.');
    }

    /**
     * Remove the specified field from storage.
     */
    public function destroy(DocumentTemplate $template, DocumentTemplateField $field): RedirectResponse
    {
        $field->delete();

        return to_route('admin.templates.edit', $template)
            ->with('success', 'Isian berhasil dihapus.');
    }

    /**
     * Ambil bagian tujuan dari request dan pastikan bagian itu milik template.
     */
    private function sectionFromRequest(SaveDocumentTemplateFieldRequest $request, DocumentTemplate $template): DocumentTemplateSection
    {
        return DocumentTemplateSection::query()
            ->whereBelongsTo($template)
            ->findOrFail($request->integer('document_template_section_id'));
    }

    /**
     * Urutan tampil berikutnya di dalam sebuah bagian.
     */
    private function nextSortOrder(DocumentTemplateSection $section, ?DocumentTemplateField $except = null): int
    {
        $query = $section->fields();

        if ($except !== null) {
            $query->where('id', '!=', $except->getKey());
        }

        return (int) $query->max('sort_order') + 1;
    }
}
