<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $transaction = Transaction::with([
            'user', 
            'details.productRecipe.product',
            'details.productRecipe.fragrance',
            'details.productRecipe.mixture',
            'details.productRecipe.bottle'
        ])->latest()->paginate(10);

        return TransactionResource::collection($transaction);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,qris,transfer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid|exists:products,id',
            'items.*.product_recipe_id' => 'nullable|uuid|exists:product_recipes,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                // $recipe = ProductRecipe::with(['product', 'fragrance', 'mixture', 'bottle'])
                //     ->findOrFail($item['product_recipe_id']);
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];

                if ($product->product_type === 'retail') {
                    if ($product->stock < $qty) {
                        return response()->json([
                            'message' => "Stok produk '{$product->name}' tidak mencukupi.",
                        ], 422);
                    }

                    $unitPrice = (float) $product->price;
                    $itemSubtotal = $unitPrice * $qty;
                    $subtotal += $itemSubtotal;

                    $itemsData[] = [
                        'type'              => 'retail',
                        'product'           => $product,
                        'product_recipe_id' => null,
                        'quantity'          => $qty,
                        'price'             => $unitPrice,
                        'subtotal'          => $itemSubtotal,
                    ];
                } else {
                    if (empty($item['product_recipe_id'])) {
                        return response()->json([
                            'message' => "Racikan untuk produk '{$product->name}' harus dipilih.",
                        ], 422);
                    }

                    $recipe = ProductRecipe::with(['product', 'fragrance', 'mixture', 'bottle'])
                        ->findOrFail($item['product_recipe_id']);

                    if ($recipe->product_id !== $product->id) {
                        return response()->json([
                            'message' => "Resep yang dipilih tidak sesuai dengan produk '{$product->name}'."
                        ], 422);
                    }
                    
                    $unitPrice = (float) $recipe->selling_price;
                    $itemSubtotal = $unitPrice * $qty;
                    $subtotal += $itemSubtotal;

                    $fragranceNeeded = (float) $recipe->fragrance_volume * $qty;
                    if ($recipe->fragrance && $recipe->fragrance->stock < $fragranceNeeded) {
                        return response()->json([
                            'message' => "Stok bibit '{$recipe->fragrance->name}' tidak mencukupi racikan {$recipe->product->name}.",
                        ], 422);
                    }

                    $mixturedNeeded = (float) $recipe->mixture_volume * $qty;
                    if ($recipe->mixture && $recipe->mixture->stock < $mixturedNeeded) {
                        return response()->json([
                            'message' => "Stok pelarut '{$recipe->mixture->name}' tidak mencukupi racikan {$recipe->product->name}.",
                        ], 422);
                    }

                    $bottleNeeded = 1 * $qty;
                    if ($recipe->bottle && $recipe->bottle->stock < $bottleNeeded) {
                        return response()->json([
                            'message' => "Stok botol '{$recipe->bottle->name}' tidak mencukupi racikan {$recipe->product->name}.",
                        ], 422);
                    }

                    $itemsData[] = [
                        'type' => 'custom',
                        'product' => $product,
                        'product_recipe_id' => $recipe->id,
                        'recipe' => $recipe,
                        'quantity' => $qty,
                        'price' => $unitPrice,
                        'subtotal' => $itemSubtotal,
                        'fragrance_needed' => $fragranceNeeded,
                        'mixture_needed' => $mixturedNeeded,
                        'bottle_needed' => $bottleNeeded,
                    ];
                }
            }

            $discount = (float) ($validated['discount'] ?? 0);
            $totalAmount = max(0, $subtotal - $discount);

            $invoiceNumber = 'INV-' . Carbon::now()->format('Ymd') . '/' . strtoupper(Str::random(6));

            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $request->user()->id,
                'customer_name' => $validated['customer_name'] ?? 'Pelanggan Umum',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'status' => 'paid',
            ]);

            foreach ($itemsData as $data) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $data['product']->id,
                    'product_recipe_id' => $data['product_recipe_id'],
                    'quantity' => $data['quantity'],
                    'price' => $data['price'],
                    'subtotal' => $data['subtotal'],
                ]);

                if ($data['type'] === 'retail') {
                    $data['product']->decrement('stock', $data['quantity']);
                } else {
                    if ($data['recipe']->fragrance) {
                        $data['recipe']->fragrance->decrement('stock', $data['fragrance_needed']);
                    }
                    if ($data['recipe']->mixture) {
                        $data['recipe']->mixture->decrement('stock', $data['mixture_needed']);
                    }
                    if ($data['recipe']->bottle) {
                        $data['recipe']->bottle->decrement('stock', $data['bottle_needed']);
                    }
                }
            }

            $transaction->load(['user', 'details.product', 'details.productRecipe.product']);

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => new TransactionResource($transaction)
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::with([
            'user', 
            'details.productRecipe.product',
            'details.productRecipe.fragrance',
            'details.productRecipe.mixture',
            'details.productRecipe.bottle',
        ])->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Data transakasi tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi ditemukan',
            'data' => new TransactionResource($transaction)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
