<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuestCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $max = max(1, (int) config('cart.max_line_quantity', 99));

        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$max],
        ];
    }
}
