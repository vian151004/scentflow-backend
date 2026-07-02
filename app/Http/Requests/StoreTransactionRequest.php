<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name'  => 'nullable|string|max:100',
            'discount'       => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qris,transfer',
            'items'          => 'required|array|min:1',
            'items.*.product_recipe_id' => 'required|exists:product_recipes,id',
            'items.*.quantity'          => 'required|integer|min:1',
        ];
    }
}
