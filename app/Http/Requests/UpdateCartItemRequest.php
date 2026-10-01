<?php

namespace App\Http\Requests;

use App\Models\CartItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('cartItem');

        return $item instanceof CartItem && $this->user()?->can('update', $item);
    }

    public function rules(): array
    {
        $max = max(1, (int) config('cart.max_line_quantity', 99));

        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$max],
        ];
    }
}
