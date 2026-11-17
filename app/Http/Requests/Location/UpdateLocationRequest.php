<?php

namespace App\Http\Requests\Location;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends FormRequest
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
        $locationId = $this->route('location')?->id ?? $this->route('location');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'uppercase', Rule::unique('locations', 'code')->ignore($locationId)],
            'type' => ['required', 'in:seaport,airport,warehouse,hub'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
