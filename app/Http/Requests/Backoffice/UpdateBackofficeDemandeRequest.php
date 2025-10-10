<?php

namespace App\Http\Requests\Backoffice;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateBackofficeDemandeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $statuses = [
            'soumission',
            'verification document',
            'validation interne',
            'accepter',
            'refuser',
            'annuler',
        ];

        return [
            'description' => ['sometimes', 'required', 'string'],
            'urgent' => ['sometimes', 'required', 'boolean'],
            'status' => ['sometimes', 'required', 'string', Rule::in($statuses)],
            'documents' => ['sometimes', 'array', 'min:1'],
            'documents.*' => ['file', 'max:5120'],
            'documents_meta' => ['sometimes', 'array'],
            'documents_meta.*.titre' => ['nullable', 'string', 'max:255'],
            'documents_to_remove' => ['sometimes', 'array'],
            'documents_to_remove.*' => ['integer', 'exists:documents,id'],
        ];
    }
}
