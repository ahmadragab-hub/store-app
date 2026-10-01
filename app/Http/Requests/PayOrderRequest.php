<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $driver = (string) config('payments.driver', 'mock');

        if ($driver === 'stripe') {
            return [
                'payment_token' => ['required', 'string', 'max:255'],
            ];
        }

        return [
            'card_number' => ['required', 'string', 'min:13', 'max:23'],
        ];
    }

    public function paymentToken(): string
    {
        if ((string) config('payments.driver', 'mock') === 'stripe') {
            return $this->string('payment_token')->toString();
        }

        return $this->string('card_number')->toString();
    }
}
