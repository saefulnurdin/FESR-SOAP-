<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveDocumentTemplateRequest;
use App\Models\DocumentTemplate;
use App\Models\DocumentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentTemplateController extends Controller
{
    /**
     * Display a listing of templates for a document type.
     */
    public function index(DocumentType $documentType): View
    {
        $templates = $documentType->templates()
            ->withCount('sections')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.templates.index', compact('documentType', 'templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create(DocumentType $documentType): View
    {
        return view('admin.templates.create', compact('documentType'));
    }

    /**
     * Store a newly created template in storage.
     */
    public function store(SaveDocumentTemplateRequest $request, DocumentType $documentType): RedirectResponse
    {
        $template = $documentType->templates()->create($request->validated());

        return to_route('admin.templates.edit', $template)
            ->with('success', 'Template berhasil dibuat. Tambahkan bagian dan isiannya.');
    }

    /**
     * Show the form for editing the specified template together with its
     * structure of sections and fields.
     */
    public function edit(DocumentTemplate $template): View
    {
        $template->load(['documentType', 'sections.fields']);

        return view('admin.templates.edit', compact('template'));
    }

    /**
     * Update the specified template in storage.
     */
    public function update(SaveDocumentTemplateRequest $request, DocumentTemplate $template): RedirectResponse
    {
        $template->update($request->validated());

        return to_route('admin.templates.edit', $template)
            ->with('success', 'Template berhasil diperbarui.');
    }

    /**
     * Remove the specified template and its structure from storage.
     */
    public function destroy(DocumentTemplate $template): RedirectResponse
    {
        $documentTypeId = $template->document_type_id;

        $template->delete();

        return to_route('admin.document-types.templates.index', $documentTypeId)
            ->with('success', 'Template beserta bagian dan isiannya berhasil dihapus.');
    }
}
