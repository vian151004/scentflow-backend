<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashClosingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'attendance_id' => $this->attendance_id,
            'system_cash_total' => (float)$this->system_cash_total,
            'physical_cash_total' => (float)$this->physical_cash_total,
            'discrepancy_amount' => (float)$this->discrepancy_amount, //minusan
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
