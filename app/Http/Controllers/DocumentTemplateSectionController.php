<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveDocumentTemplateSectionRequest;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateSection;
use Illuminate\Http\RedirectResponse;

class DocumentTemplateSectionController extends Controller
{
    /**
     * Store a newly created section in storage.
     */
    public function store(SaveDocumentTemplateSectionRequest $request, DocumentTemplate $template): RedirectResponse
    {
        $template->sections()->create([
            ...$request->safe()->only(['key', 'title', 'hint']),
            'sort_order' => (int) $template->sections()->max('sort_order') + 1,
        ]);

        return to_route('admin.templates.edit', $template)
            ->with('success', 'Bagian berhasil ditambahkan.');
    }

    /**
     * Remove the specified section and its fields from storage.
     */
    public function destroy(DocumentTemplate $template, DocumentTemplateSection $section): RedirectResponse
    {
        $section->delete();

        return to_route('admin.templates.edit', $template)
            ->with('success', 'Bagian beserta isiannya berhasil dihapus.');
    }
}
