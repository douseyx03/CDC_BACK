<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDemandeRequest;
use App\Http\Requests\UpdateDemandeRequest;
use App\Models\Demande;
use App\Services\Demande\DemandeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DemandeController extends Controller
{
    public function __construct(private readonly DemandeService $demandeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Demande::with(['documents', 'service']);

        if ($user->isAdmin()) {
            if ($type = $request->query('type')) {
                $query->where('type_demande', $this->formatType($type));
            }

            if ($status = $request->query('status')) {
                $query->where('status', $status);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        $demandes = $query
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($demandes);
    }

    public function store(StoreDemandeRequest $request): JsonResponse
    {
        $demande = $this->demandeService->create($request->user(), $request->validated());

        return response()->json($demande, 201);
    }

    public function show(Demande $demande): JsonResponse
    {
        $this->authorize('view', $demande);

        return response()->json($demande->load(['documents', 'service']));
    }

    public function update(UpdateDemandeRequest $request, Demande $demande): JsonResponse
    {
        $demande = $this->demandeService->update($demande, $request->validated(), $request->user());

        return response()->json($demande);
    }

    public function destroy(Demande $demande): JsonResponse
    {
        $this->authorize('delete', $demande);

        $demande->delete();

        return response()->json(null, 204);
    }

    private function formatType(string $type): string
    {
        return match (strtolower($type)) {
            'particulier' => 'Particulier',
            'entreprise' => 'Entreprise',
            'institution' => 'Institution',
            default => Str::title($type),
        };
    }
}
