<?php

namespace App\Http\Requests;

class StoreAgentRequest extends ApiFormRequest
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
        return [
            'division' => ['bail', 'required', 'string', 'max:255'],
            'matricule' => ['bail', 'required', 'string', 'max:100', 'unique:agents,matricule'],
            'poste' => ['bail', 'required', 'string', 'max:255'],
            'user_id' => ['bail', 'required', 'integer', 'exists:users,id', 'unique:agents,user_id'],
        ];
    }
}
