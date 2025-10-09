<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class RegisterRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'prenom' => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'email' => ['bail', 'required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'telephone' => ['bail', 'required', 'string', 'regex:/^\+?[0-9]{7,15}$/', 'unique:users,telephone'],
            'password' => ['required', 'string', 'min:8', 'max:64', 'confirmed'],
            'type_utilisateur' => ['bail', 'required', 'string', 'in:particulier,entreprise,institution'],
            'nom_entreprise' => ['nullable', 'string', 'min:2', 'max:255'],
            'type_entreprise' => ['nullable', 'string', 'in:pme,grande_entreprise,startup,cooperative'],
            'nom_institution' => ['nullable', 'string', 'min:2', 'max:255'],
            'type_institution' => ['nullable', 'string', 'in:banque,assurance,microfinance,institution_gouvernementale'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type_utilisateur');

            if ($type === 'entreprise') {
                foreach (['nom_entreprise', 'type_entreprise'] as $field) {
                    if (!$this->filled($field)) {
                        $validator->errors()->add($field, 'Ce champ est requis pour un utilisateur entreprise.');
                    }
                }
            }

            if ($type === 'institution') {
                foreach (['nom_institution', 'type_institution'] as $field) {
                    if (!$this->filled($field)) {
                        $validator->errors()->add($field, 'Ce champ est requis pour un utilisateur institution.');
                    }
                }
            }
        });
    }
}
