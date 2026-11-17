<?php

namespace App\Http\Requests\Exception;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentExceptionRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:OPEN,INVESTIGATING,RESOLVED,open,investigating,resolved'],
            'severity' => ['nullable', 'string', 'in:LOW,MEDIUM,HIGH,CRITICAL,low,medium,high,critical'],
            'description' => ['nullable', 'string', 'max:1500'],
            'resolution_notes' => ['nullable', 'string', 'max:1500'],
        ];
    }
}
