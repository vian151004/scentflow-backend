<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
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
            'sku'               => $this->sku,
            'name'              => $this->name,
            'category'          => $this->category,
            'stock'             => (float) $this->stock,
            'unit'              => $this->unit,
            'threshold_minimum' => (float) $this->threshold_minimum,
        ];
    }
}
