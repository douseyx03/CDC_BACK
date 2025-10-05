<?php

namespace App\Http\Requests;

use App\Models\Demande;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDemandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Demande::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'type_demande' => ['bail', 'required', 'string', Rule::in(['particulier', 'entreprise', 'institution'])],
            'description' => ['bail', 'required', 'string'],
            'urgent' => ['sometimes', 'boolean'],
            'service_id' => ['bail', 'required', 'integer', 'exists:services,id'],
            'documents' => ['sometimes', 'array', 'min:1'],
            'documents.*' => ['file', 'max:5120'],
            'documents_meta' => ['sometimes', 'array'],
            'documents_meta.*.titre' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function prepareForValidation(): void
    {
        if ($this->has('type_demande')) {
            $this->merge([
                'type_demande' => strtolower($this->input('type_demande')),
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $type = $this->input('type_demande');

            if ($user && !$user->ensureProfileType($type)) {
                $validator->errors()->add('type_demande', 'Le type de demande doit correspondre à votre profil.');
            }
        });
    }
}
