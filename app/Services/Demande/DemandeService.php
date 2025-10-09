<?php

namespace App\Services\Demande;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DemandeService
{
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly DatabaseManager $db,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Demande
    {
        return $this->db->transaction(function () use ($user, $data) {
            $documents = $data['documents'] ?? [];
            $documentsMeta = $data['documents_meta'] ?? [];

            $demande = Demande::create([
                'type_demande' => $this->formatType($data['type_demande']),
                'description' => $data['description'],
                'urgent' => Arr::get($data, 'urgent', false),
                'status' => 'soumission',
                'service_id' => $data['service_id'],
                'user_id' => $user->id,
            ]);

            if (!empty($documents)) {
                $this->documentService->storeMany($demande, $documents, $documentsMeta, $user->id);
            }

            return $demande->load('documents');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Demande $demande, array $data, User $user): Demande
    {
        return $this->db->transaction(function () use ($demande, $data, $user) {
            $demande->fill(Arr::only($data, ['description', 'urgent', 'status']));
            $demande->save();

            if (!empty($data['documents'])) {
                $this->documentService->storeMany(
                    $demande,
                    $data['documents'],
                    $data['documents_meta'] ?? [],
                    $user->id
                );
            }

            if (!empty($data['documents_to_remove'])) {
                $documents = $demande->documents()->whereIn('id', $data['documents_to_remove'])->get();

                foreach ($documents as $document) {
                    $this->documentService->delete($document);
                }
            }

            return $demande->fresh('documents');
        });
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
