<?php

namespace App\Http\Requests;

use App\Models\Demande;
use Illuminate\Validation\Rule;

class UpdateDemandeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $demande = $this->route('demande');

        return $demande instanceof Demande
            ? ($this->user()?->can('update', $demande) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'required', 'string'],
            'urgent' => ['sometimes', 'required', 'boolean'],
            'status' => ['sometimes', 'required', 'string', Rule::in([
                'soumission',
                'verification document',
                'validation interne',
                'accepter',
                'refuser',
                'annuler',
            ])],
            'documents' => ['sometimes', 'array', 'min:1'],
            'documents.*' => ['file', 'max:5120'],
            'documents_meta' => ['sometimes', 'array'],
            'documents_meta.*.titre' => ['nullable', 'string', 'max:255'],
            'documents_to_remove' => ['sometimes', 'array'],
            'documents_to_remove.*' => ['integer', 'exists:documents,id'],
        ];
    }
}
