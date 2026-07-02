<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['sku', 'name', 'category', 'stock', 'unit', 'threshold_minimum'])]
class Material extends Model
{
    use HasUuids;
}
