<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MaterialResource;

class ProductRecipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'product_id'       => $this->product_id,
            'bottle_size'      => $this->bottle_size,
            'ratio_type'       => $this->ratio_type,
            'selling_price'    => (float) $this->selling_price,
            
            // Detail Fragrance (Bibit)
            'fragrance_volume' => (float) $this->fragrance_volume,
            'fragrance'        => new MaterialResource($this->whenLoaded('fragrance')),
            
            // Detail Mixture (Campuran/Pelarut)
            'mixture_volume'   => (float) $this->mixture_volume,
            'mixture'          => new MaterialResource($this->whenLoaded('mixture')),
            
            // Detail Bottle (Botol)
            'bottle'           => new MaterialResource($this->whenLoaded('bottle')),
        ];
    }
}