<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'category' => $this->category,
            'description' => $this->description,
            'base_price_per_ml' => (float) $this->base_price_per_ml,
            'image_url' => $this->image ? asset('storage/' . $this->image) : asset('images/default-perfume.png'),
            'is_available' => (boolean) $this->is_available,
            'recipes' => ProductRecipeResource::collection($this->whenLoaded('recipes')),
        ];
    }
}
