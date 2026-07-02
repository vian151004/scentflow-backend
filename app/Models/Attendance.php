<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id', 
    'date', 
    'clock_in', 
    'clock_out', 
    'initial_cash', 
    'image_proof'
])]

class Attendance extends Model
{
    use HasUuids;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashClosing(): HasOne
    {
        return $this->hasOne(CashClosing::class);
    }
}
