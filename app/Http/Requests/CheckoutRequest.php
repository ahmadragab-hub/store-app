<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_line1' => ['required', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_state' => ['nullable', 'string', 'max:100'],
            'shipping_postal' => ['required', 'string', 'max:20'],
            'shipping_country' => ['required', 'string', 'size:2'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('shipping_country')) {
            $this->merge([
                'shipping_country' => strtoupper((string) $this->input('shipping_country')),
            ]);
        }
    }


    public function shipping(): array
    {
        return [
            'shipping_name' => $this->string('shipping_name')->toString(),
            'shipping_line1' => $this->string('shipping_line1')->toString(),
            'shipping_city' => $this->string('shipping_city')->toString(),
            'shipping_state' => $this->filled('shipping_state') ? $this->string('shipping_state')->toString() : null,
            'shipping_postal' => $this->string('shipping_postal')->toString(),
            'shipping_country' => $this->string('shipping_country')->toString(),
        ];
    }
}
