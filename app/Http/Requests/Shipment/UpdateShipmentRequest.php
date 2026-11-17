<?php

namespace App\Http\Requests\Shipment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShipmentRequest extends FormRequest
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
        $shipmentId = $this->route('shipment')?->id ?? $this->route('shipment');

        return [
            'shipment_number' => ['required', 'string', 'max:50', Rule::unique('shipments', 'shipment_number')->ignore($shipmentId)],
            'customer_id' => ['required', 'exists:users,id'],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'origin_id' => ['required', 'exists:locations,id'],
            'destination_id' => ['required', 'exists:locations,id', 'different:origin_id'],
            'cargo_type' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:0.01'],
            'volume' => ['required', 'numeric', 'min:0.01'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date'],
            'estimated_arrival' => ['required', 'date'],
            'actual_departure' => ['nullable', 'date'],
            'actual_arrival' => ['nullable', 'date'],
            'current_status' => ['required', 'in:pending,in_transit,customs_clearance,delivered,delayed,cancelled'],
            'is_delayed' => ['boolean'],
            'delay_hours' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            // Items validation
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.item_code' => ['nullable', 'string', 'max:100'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.weight' => ['required', 'numeric', 'min:0.01'],
            'items.*.volume' => ['nullable', 'numeric', 'min:0'],
            'items.*.declared_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
