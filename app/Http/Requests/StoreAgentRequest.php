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
            'nom' => ['bail', 'required', 'string', 'max:255'],
            'prenom' => ['bail', 'required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'email:rfc', 'max:255', 'unique:users,email'],
            'telephone' => ['bail', 'required', 'string', 'regex:/^\+?[0-9]{7,15}$/', 'unique:users,telephone'],
            'division' => ['bail', 'required', 'string', 'max:255'],
            'matricule' => ['bail', 'required', 'string', 'max:100', 'unique:agents,matricule'],
            'poste' => ['bail', 'required', 'string', 'max:255'],
            'roles' => ['bail', 'required', 'array', 'min:1'],
            'roles.*' => ['bail', 'string', 'exists:roles,name'],
        ];
    }
}
