<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class VerifyPhoneRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['bail', 'required', 'string', 'regex:/^[0-9]{6}$/'],
            'email' => ['nullable', 'string', 'email:rfc', 'exists:users,email'],
            'telephone' => ['nullable', 'string', 'regex:/^\+?[0-9]{7,15}$/', 'exists:users,telephone'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->user()) {
                return;
            }

            if (!$this->filled('email') && !$this->filled('telephone')) {
                $validator->errors()->add('identifier', 'Fournissez une adresse e-mail ou un numéro de téléphone.');
            }
        });
    }
}
