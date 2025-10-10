<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateRoleRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = $this->route('role');
        $roleId = $role?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($roleId)],
            'guard_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'permissions' => ['sometimes', 'nullable', 'array'],
            'permissions.*' => ['bail', 'string', 'exists:permissions,name'],
        ];
    }
}
