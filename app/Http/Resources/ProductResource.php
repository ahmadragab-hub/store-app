<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $admin = $request->user()?->isAdmin() === true;

        $data = [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image_src,
            'price' => $this->price,
            'in_stock' => (int) $this->stock > 0,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];

        if ($admin) {
            $data['stock'] = $this->stock;
            $data['status'] = $this->status;
        }

        return $data;
    }
}
