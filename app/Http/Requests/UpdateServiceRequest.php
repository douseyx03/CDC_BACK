<?php

namespace App\Http\Requests;

use App\Models\Service;

class UpdateServiceRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');

        return $service instanceof Service
            ? ($this->user()?->can('update', $service) ?? false)
            : ($this->user()?->can('update', Service::class) ?? false);
    }

    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'avantage' => ['sometimes', 'required', 'array', 'min:1'],
            'avantage.*' => ['bail', 'string', 'min:1'],
            'delai' => ['sometimes', 'required', 'string', 'max:255'],
            'montant_min' => ['sometimes', 'required', 'numeric', 'min:0'],
            'document_requis' => ['sometimes', 'required', 'array', 'min:1'],
            'document_requis.*' => ['bail', 'string', 'min:1'],
        ];
    }
}
