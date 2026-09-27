<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveDocumentTypeRequest;
use App\Models\DocumentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentTypeController extends Controller
{
    /**
     * Display a listing of document types.
     */
    public function index(): View
    {
        $documentTypes = DocumentType::query()
            ->withCount('templates')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.document-types.index', compact('documentTypes'));
    }

    /**
     * Show the form for creating a new document type.
     */
    public function create(): View
    {
        return view('admin.document-types.create');
    }

    /**
     * Store a newly created document type in storage.
     */
    public function store(SaveDocumentTypeRequest $request): RedirectResponse
    {
        DocumentType::create($request->validated());

        return to_route('admin.document-types.index')
            ->with('success', 'Jenis dokumen berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified document type.
     */
    public function edit(DocumentType $documentType): View
    {
        return view('admin.document-types.edit', compact('documentType'));
    }

    /**
     * Update the specified document type in storage.
     */
    public function update(SaveDocumentTypeRequest $request, DocumentType $documentType): RedirectResponse
    {
        $documentType->update($request->validated());

        return to_route('admin.document-types.index')
            ->with('success', 'Jenis dokumen berhasil diperbarui.');
    }

    /**
     * Remove the specified document type from storage.
     *
     * Jenis dokumen yang masih memiliki template tidak dihapus, supaya
     * struktur yang sudah disiapkan tidak hilang karena salah klik.
     */
    public function destroy(DocumentType $documentType): RedirectResponse
    {
        if ($documentType->templates()->exists()) {
            return to_route('admin.document-types.index')
                ->with('error', 'Jenis dokumen masih memiliki template. Hapus atau pindahkan templatenya lebih dahulu.');
        }

        $documentType->delete();

        return to_route('admin.document-types.index')
            ->with('success', 'Jenis dokumen berhasil dihapus.');
    }
}
