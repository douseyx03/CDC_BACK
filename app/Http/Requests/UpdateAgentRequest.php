<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateAgentRequest extends ApiFormRequest
{
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
        $agentId = $this->route('agent')?->getKey();

        return [
            'division' => ['sometimes', 'required', 'string', 'max:255'],
            'matricule' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('agents', 'matricule')->ignore($agentId)],
            'poste' => ['sometimes', 'required', 'string', 'max:255'],
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id', Rule::unique('agents', 'user_id')->ignore($agentId)],
        ];
    }
}
