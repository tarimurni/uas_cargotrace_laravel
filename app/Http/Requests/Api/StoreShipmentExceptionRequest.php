<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exception_type' => [
                'required',
                'string',
                'in:CUSTOMS_HOLD,WEATHER_DELAY,PORT_CONGESTION,DAMAGED,MISROUTED,DOCUMENTATION_ISSUE',
            ],
            'severity' => ['required', 'string', 'in:LOW,MEDIUM,HIGH,CRITICAL'],
            'description' => ['required', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'in:OPEN,INVESTIGATING,RESOLVED'],
            'detected_at' => ['nullable', 'date'],
            'resolution_notes' => ['nullable', 'string'],
        ];
    }
}
