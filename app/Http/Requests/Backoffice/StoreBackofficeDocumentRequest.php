<?php

namespace App\Http\Requests\Backoffice;

use App\Http\Requests\ApiFormRequest;

class StoreBackofficeDocumentRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['file', 'max:5120'],
            'documents_meta' => ['sometimes', 'array'],
            'documents_meta.*.titre' => ['nullable', 'string', 'max:255'],
        ];
    }
}
