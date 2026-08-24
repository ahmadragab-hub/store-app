<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = (float) ($this->product?->price ?? 0);

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'line_total' => number_format($this->quantity * $price, 2, '.', ''),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
