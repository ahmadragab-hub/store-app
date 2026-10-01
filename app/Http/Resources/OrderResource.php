<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $admin = $request->user()?->isAdmin() === true;

        return [
            'id' => $this->id,
            'user_id' => $this->when($admin, $this->user_id),
            'total' => $this->total,
            'status' => $this->status,
            'shipping_name' => $this->shipping_name,
            'shipping_line1' => $this->shipping_line1,
            'shipping_city' => $this->shipping_city,
            'shipping_state' => $this->shipping_state,
            'shipping_postal' => $this->shipping_postal,
            'shipping_country' => $this->shipping_country,
            'payment_driver' => $this->payment_driver,
            'paid_at' => $this->paid_at,
            'shipped_at' => $this->shipped_at,
            'cancelled_at' => $this->cancelled_at,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }
}
