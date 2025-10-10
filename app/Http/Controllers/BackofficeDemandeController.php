<?php

namespace App\Http\Controllers;

use App\Http\Requests\Backoffice\StoreBackofficeDocumentRequest;
use App\Http\Requests\Backoffice\UpdateBackofficeDemandeRequest;
use App\Http\Requests\Backoffice\UpdateBackofficeDocumentRequest;
use App\Models\Demande;
use App\Models\Document;
use App\Services\Demande\DemandeService;
use App\Services\Demande\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BackofficeDemandeController extends Controller
{
    public function __construct(
        private readonly DemandeService $demandeService,
        private readonly DocumentService $documentService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Demande::query()
            ->with(['user', 'service'])
            ->latest();

        if ($type = $request->query('type')) {
            $query->where('type_demande', $type);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if (!is_null($request->query('urgent'))) {
            $query->where('urgent', filter_var($request->query('urgent'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($serviceId = $request->query('service_id')) {
            $query->where('service_id', $serviceId);
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($from = $request->query('created_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('created_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $demandes = $query->paginate($request->integer('per_page', 15));

        return response()->json($demandes);
    }

    public function show(Demande $demande): JsonResponse
    {
        return response()->json(
            $demande->load(['user', 'service', 'documents'])
        );
    }

    public function update(UpdateBackofficeDemandeRequest $request, Demande $demande): JsonResponse
    {
        $demande = $this->demandeService->update(
            $demande,
            $request->validated(),
            $request->user()
        );

        return response()->json($demande->load(['user', 'service', 'documents']));
    }

    public function storeDocument(StoreBackofficeDocumentRequest $request, Demande $demande): JsonResponse
    {
        $data = $request->validated();

        $this->documentService->storeMany(
            $demande,
            $data['documents'],
            $data['documents_meta'] ?? [],
            $request->user()->id
        );

        return response()->json($demande->fresh('documents'), 201);
    }

    public function updateDocument(UpdateBackofficeDocumentRequest $request, Document $document): JsonResponse
    {
        $document->update($request->validated());

        return response()->json($document);
    }

    public function destroyDocument(Document $document): JsonResponse
    {
        $this->documentService->delete($document);

        return response()->json(null, 204);
    }
}
