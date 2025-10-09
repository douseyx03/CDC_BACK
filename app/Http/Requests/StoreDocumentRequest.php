<?php

namespace App\Http\Requests;

use App\Models\Demande;
use App\Models\Document;

class StoreDocumentRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $demande = $this->route('demande');

        return $demande instanceof Demande
            ? ($this->user()?->can('create', [Document::class, $demande]) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'titre' => ['nullable', 'string', 'max:255'],
            'fichier' => ['required', 'file', 'max:5120'],
        ];
    }
}
