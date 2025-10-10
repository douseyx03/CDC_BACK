<?php

namespace App\Http\Requests;

class StoreRoleRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['bail', 'required', 'string', 'max:255', 'unique:roles,name'],
            'guard_name' => ['bail', 'nullable', 'string', 'max:255'],
            'permissions' => ['bail', 'nullable', 'array'],
            'permissions.*' => ['bail', 'string', 'exists:permissions,name'],
        ];
    }
}
