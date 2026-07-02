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
    'bibit_material_id', 
    'bibit_volume', 
    'campuran_material_id', 
    'campuran_volume', 
    'botol_material_id', 
    'selling_price'
])]

class ProductRecipe extends Model
{
    use HasUuids;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function bibit(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'bibit_material_id');
    }

    public function campuran(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'campuran_material_id');
    }

    public function botol(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'botol_material_id');
    }
}
