<?php

namespace App\Http\Requests;

use App\Models\Document;

class UpdateDocumentRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $document instanceof Document
            ? ($this->user()?->can('update', $document) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'titre' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}
