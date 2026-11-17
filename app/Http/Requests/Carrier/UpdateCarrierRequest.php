<?php

namespace App\Http\Requests\Carrier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCarrierRequest extends FormRequest
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
        $carrierId = $this->route('carrier')?->id ?? $this->route('carrier');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'uppercase', Rule::unique('carriers', 'code')->ignore($carrierId)],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'service_type' => ['required', 'in:sea,air,land,multimodal'],
            'is_active' => ['boolean'],
        ];
    }
}
