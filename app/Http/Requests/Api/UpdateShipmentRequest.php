<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $shipmentId = $this->route('shipment') instanceof \App\Models\Shipment
            ? $this->route('shipment')->id
            : ($this->route('shipment') ?? $this->route('id'));

        return [
            'customer_id' => ['sometimes', 'required', 'exists:users,id'],
            'carrier_id' => ['sometimes', 'required', 'exists:carriers,id'],
            'origin_id' => ['sometimes', 'required', 'exists:locations,id'],
            'destination_id' => ['sometimes', 'required', 'exists:locations,id'],
            'cargo_type' => ['sometimes', 'required', 'string', 'max:100'],
            'weight' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'volume' => ['nullable', 'numeric', 'min:0'],
            'expected_departure' => ['sometimes', 'required', 'date'],
            'expected_arrival' => ['sometimes', 'required', 'date'],
            'estimated_arrival' => ['nullable', 'date'],
            'current_status' => ['sometimes', 'required', 'string', 'max:50'],
            'shipment_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('shipments', 'shipment_number')->ignore($shipmentId),
            ],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.item_name' => ['required_with:items', 'string', 'max:255'],
            'items.*.item_code' => ['nullable', 'string', 'max:100'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.weight' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.volume' => ['nullable', 'numeric', 'min:0'],
            'items.*.declared_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
