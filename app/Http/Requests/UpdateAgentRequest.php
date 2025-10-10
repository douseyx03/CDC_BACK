<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateAgentRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $roles = $this->input('roles');

        if (is_string($roles)) {
            $normalized = array_filter(array_map('trim', preg_split('/[,;]+/', $roles)));
            $this->merge(['roles' => $normalized]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $agent = $this->route('agent');
        $agentId = $agent?->getKey();
        $userId = $agent?->user_id;

        return [
            'nom' => ['sometimes', 'required', 'string', 'max:255'],
            'prenom' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'telephone' => ['sometimes', 'required', 'string', 'regex:/^\+?[0-9]{7,15}$/', Rule::unique('users', 'telephone')->ignore($userId)],
            'division' => ['sometimes', 'required', 'string', 'max:255'],
            'matricule' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('agents', 'matricule')->ignore($agentId)],
            'poste' => ['sometimes', 'required', 'string', 'max:255'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => [
                'bail',
                'string',
                Rule::exists('roles', 'name')->where(fn ($query) => $query->where('guard_name', 'sanctum')),
            ],
        ];
    }
}
