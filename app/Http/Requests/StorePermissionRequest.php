<?php

namespace App\Http\Requests;

class StorePermissionRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['bail', 'required', 'string', 'max:255', 'unique:permissions,name'],
            'guard_name' => ['bail', 'nullable', 'string', 'max:255'],
        ];
    }
}
