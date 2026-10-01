<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $max = max(1, (int) config('cart.max_line_quantity', 99));

        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('status', 'active')],
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$max],
        ];
    }
}
