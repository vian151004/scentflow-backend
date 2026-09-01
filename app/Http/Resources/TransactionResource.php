<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'cashier' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'customer_name' => $this->customer_name,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'total_amount' => (float) $this->total_amount,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'details' => TransactionDetailResource::collection($this->whenLoaded('details')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
