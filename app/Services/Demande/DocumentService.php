<?php

namespace App\Services\Demande;

use App\Models\Demande;
use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    public function store(Demande $demande, UploadedFile $file, array $meta, int $userId): Document
    {
        $path = $file->store('documents', 'public');

        return $demande->documents()->create([
            'titre' => $meta['titre'] ?? Str::title(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            'path' => $path,
            'user_id' => $userId,
        ]);
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @param  array<int, array<string, mixed>>  $meta
     * @return array<int, Document>
     */
    public function storeMany(Demande $demande, array $files, array $meta, int $userId): array
    {
        $documents = [];

        foreach ($files as $index => $file) {
            $documents[] = $this->store($demande, $file, $meta[$index] ?? [], $userId);
        }

        return $documents;
    }

    public function delete(Document $document): void
    {
        Storage::disk('public')->delete($document->path);
        $document->delete();
    }
}
