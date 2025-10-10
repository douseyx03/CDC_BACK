<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permission = $this->route('permission');
        $permissionId = $permission?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permissionId)],
            'guard_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
