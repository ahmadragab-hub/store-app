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
        return [
            'card_number' => ['required', 'string', 'min:13', 'max:23'],
        ];
    }
}
