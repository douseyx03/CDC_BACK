<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class LoginRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['nullable', 'string', 'email:rfc', 'required_without:telephone'],
            'telephone' => ['nullable', 'string', 'regex:/^\+?[0-9]{7,15}$/', 'required_without:email'],
            'password' => ['required', 'string', 'min:8', 'max:64'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

}
