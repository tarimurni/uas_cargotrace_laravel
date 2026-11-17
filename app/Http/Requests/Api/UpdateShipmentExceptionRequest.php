<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:OPEN,INVESTIGATING,RESOLVED'],
            'severity' => ['nullable', 'string', 'in:LOW,MEDIUM,HIGH,CRITICAL'],
            'description' => ['nullable', 'string', 'max:1000'],
            'resolution_notes' => ['nullable', 'string'],
        ];
    }
}
