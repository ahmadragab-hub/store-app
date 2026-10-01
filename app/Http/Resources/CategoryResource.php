<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $admin = $request->user()?->isAdmin() === true;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image_src,
            'products_count' => $this->when($admin && isset($this->products_count), $this->products_count),
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
