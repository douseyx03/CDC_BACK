<?php

namespace App\Http\Requests;

use App\Models\Service;

class StoreServiceRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Service::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'nom' => ['bail', 'required', 'string', 'max:255'],
            'description' => ['bail', 'required', 'string'],
            'avantage' => ['bail', 'required', 'array', 'min:1'],
            'avantage.*' => ['bail', 'string', 'min:1'],
            'delai' => ['bail', 'required', 'string', 'max:255'],
            'montant_min' => ['bail', 'required', 'numeric', 'min:0'],
            'document_requis' => ['bail', 'required', 'array', 'min:1'],
            'document_requis.*' => ['bail', 'string', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['avantage', 'document_requis'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $decoded = json_decode($value, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $this->merge([$field => $decoded]);
                }
            }
        }
    }
}
