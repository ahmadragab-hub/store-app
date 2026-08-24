<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $total = $this->items->sum(function ($item) {
            return $item->quantity * (float) ($item->product?->price ?? 0);
        });

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'total' => number_format($total, 2, '.', ''),
            'items' => CartItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
