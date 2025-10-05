<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Demande;
use App\Models\Document;
use App\Services\Demande\DocumentService;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentService $documentService)
    {
    }

    public function index(Demande $demande): JsonResponse
    {
        $this->authorize('view', $demande);

        return response()->json($demande->documents()->latest()->get());
    }

    public function store(StoreDocumentRequest $request, Demande $demande): JsonResponse
    {
        $document = $this->documentService->store(
            $demande,
            $request->file('fichier'),
            $request->only('titre'),
            $request->user()->id
        );

        return response()->json($document, 201);
    }

    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        return response()->json($document);
    }

    public function update(UpdateDocumentRequest $request, Document $document): JsonResponse
    {
        $document->update($request->validated());

        return response()->json($document->fresh());
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        $this->documentService->delete($document);

        return response()->json(null, 204);
    }
}
