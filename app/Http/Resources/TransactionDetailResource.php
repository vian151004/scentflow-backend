<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'product_id'        => $this->product_id,
            'product_name'      => $this->product->name ?? $this->productRecipe?->product?->name,
            'product_recipe_id' => $this->product_recipe_id,
            'quantity'          => (int) $this->quantity,
            'price'             => (float) $this->price,
            'subtotal'          => (float) $this->subtotal,
        ];
    }
}
