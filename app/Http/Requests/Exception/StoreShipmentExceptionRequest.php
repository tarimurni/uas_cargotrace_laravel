<?php

namespace App\Http\Requests\Exception;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentExceptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isAdmin() || $user->isOperator());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exception_type' => [
                'required',
                'string',
                'in:CUSTOMS_HOLD,WEATHER_DELAY,PORT_CONGESTION,DAMAGED,MISROUTED,DOCUMENTATION_ISSUE,customs_hold,weather_delay,port_congestion,damaged,misrouted,documentation_issue',
            ],
            'severity' => ['required', 'string', 'in:LOW,MEDIUM,HIGH,CRITICAL,low,medium,high,critical'],
            'status' => ['required', 'string', 'in:OPEN,INVESTIGATING,RESOLVED,open,investigating,resolved'],
            'description' => ['required', 'string', 'max:1500'],
            'detected_at' => ['nullable', 'date'],
            'resolution_notes' => ['nullable', 'string', 'max:1500'],
        ];
    }
}
