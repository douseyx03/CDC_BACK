<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'bail|required|string|min:2|max:255',
            'prenom' => 'bail|required|string|min:2|max:255',
            'email' => 'bail|required|string|email:rfc|max:255|unique:users,email',
            'telephone' => 'bail|required|string|regex:/^\+?[0-9]{7,15}$/|unique:users,telephone',
            'password' => 'required|string|min:8|max:64|confirmed',
        ];
    }
}
