<?php

namespace App\Http\Requests\Backoffice;

use App\Http\Requests\ApiFormRequest;

class UpdateBackofficeDocumentRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
        ];
    }
}
