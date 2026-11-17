<?php

namespace App\Http\Requests\Carrier;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarrierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'uppercase', 'unique:carriers,code'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'service_type' => ['required', 'in:sea,air,land,multimodal'],
            'is_active' => ['boolean'],
        ];
    }
}
