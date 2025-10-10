<?php

namespace App\Http\Controllers;

use App\Http\Requests\Backoffice\StoreBackofficeDocumentRequest;
use App\Http\Requests\Backoffice\UpdateBackofficeDemandeRequest;
use App\Http\Requests\Backoffice\UpdateBackofficeDocumentRequest;
use App\Models\Demande;
use App\Models\Document;
use App\Notifications\DemandeUpdatedNotification;
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
        $oldStatus = $demande->status;
        $oldUrgent = $demande->urgent;
        $oldDescription = $demande->description;

        $addedFilesCount = $request->hasFile('documents') ? count($request->file('documents')) : 0;
        $removedDocsCount = count($request->input('documents_to_remove', []));

        $demande = $this->demandeService->update(
            $demande,
            $request->validated(),
            $request->user()
        );

        $messages = [];

        if ($oldStatus !== $demande->status) {
            $messages[] = sprintf(
                "Statut changé de ‘%s’ à ‘%s’.",
                $oldStatus ?? 'non défini',
                $demande->status ?? 'non défini'
            );
        }

        if ($request->filled('urgent') && $oldUrgent !== $demande->urgent) {
            $messages[] = $demande->urgent
                ? 'La demande est désormais marquée comme urgente.'
                : 'La demande n’est plus marquée comme urgente.';
        }

        if ($request->filled('description') && $oldDescription !== $demande->description) {
            $messages[] = 'La description de la demande a été mise à jour.';
        }

        if ($addedFilesCount > 0) {
            $messages[] = $addedFilesCount.' document(s) ont été ajoutés à votre demande.';
        }

        if ($removedDocsCount > 0) {
            $messages[] = $removedDocsCount.' document(s) ont été supprimés de votre demande.';
        }

        if (!empty($messages) && $demande->user) {
            $demande->user->notify(new DemandeUpdatedNotification($demande, $messages));
        }

        return response()->json($demande->load(['user', 'service', 'documents']));
    }

    public function storeDocument(StoreBackofficeDocumentRequest $request, Demande $demande): JsonResponse
    {
        $data = $request->validated();

        $stored = $this->documentService->storeMany(
            $demande,
            $data['documents'],
            $data['documents_meta'] ?? [],
            $request->user()->id
        );

        $demande = $demande->fresh('documents');
        $demande->loadMissing(['user', 'service']);

        if (!empty($stored) && $demande?->user) {
            $demande->user->notify(new DemandeUpdatedNotification(
                $demande,
                [count($stored).' document(s) ont été ajoutés à votre demande.']
            ));
        }

        return response()->json($demande, 201);
    }

    public function updateDocument(UpdateBackofficeDocumentRequest $request, Document $document): JsonResponse
    {
        $demande = $document->demande;
        $oldTitle = $document->titre;

        $document->update($request->validated());

        $demande = $demande?->fresh('documents');
        $demande?->loadMissing(['user', 'service']);

        if ($demande && $demande->user) {
            $demande->user->notify(new DemandeUpdatedNotification(
                $demande,
                [sprintf("Le document ‘%s’ a été renommé en ‘%s’.", $oldTitle, $document->titre)]
            ));
        }

        return response()->json($document);
    }

    public function destroyDocument(Document $document): JsonResponse
    {
        $demande = $document->demande;
        $titre = $document->titre;

        $this->documentService->delete($document);

        $demande = $demande?->fresh('documents');
        $demande?->loadMissing(['user', 'service']);

        if ($demande && $demande->user) {
            $demande->user->notify(new DemandeUpdatedNotification(
                $demande,
                [sprintf("Le document ‘%s’ a été supprimé.", $titre)]
            ));
        }

        return response()->json(null, 204);
    }
}
