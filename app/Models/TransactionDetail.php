<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'transaction_id', 
    'product_recipe_id', 
    'quantity', 
    'price', 
    'subtotal'
])]

class TransactionDetail extends Model
{
    use HasUuids;

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(ProductRecipe::class, 'product_recipe_id');
    }
}
