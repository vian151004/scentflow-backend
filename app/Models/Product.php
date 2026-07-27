<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 
    'slug', 
    'sku', 
    'category', 
    'description', 
    'base_price_per_ml', 
    'image', 
    'is_available'
])]

class Product extends Model
{
    use HasUuids;

    public function recipes(): HasMany
    {
        return $this->hasMany(ProductRecipe::class, 'product_id');
    }
}
