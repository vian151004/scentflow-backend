<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id', 
    'bottle_size', 
    'ratio_type', 
    'fragrance_material_id', 
    'fragrance_volume', 
    'mixture_material_id', 
    'mixture_volume', 
    'bottle_material_id', 
    'selling_price'
])]
class ProductRecipe extends Model
{
    use HasUuids;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function fragrance(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'fragrance_material_id');
    }

    public function mixture(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'mixture_material_id');
    }

    public function bottle(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'bottle_material_id');
    }
}