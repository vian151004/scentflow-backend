<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductRecipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'bottle_size' => $this->bottle_size, 
            'ratio_type' => $this->ratio_type,   
            'selling_price' => (int) $this->selling_price, 
            'bibit_volume' => (float) $this->bibit_volume,
            'campuran_volume' => (float) $this->campuran_volume,
        ];
    }
}