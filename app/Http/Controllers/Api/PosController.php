<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductRecipe;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function storeTransaction(Request $request)
    {
        $user = $request->user(); // Ambil data kasir yang sedang login

        // 1. Generate Nomor Nota Kronologis (Format: TRX-YYYYMMDD-IDACAK)
        $dateString = Carbon::now()->format('Ymd');
        $invoiceNumber = 'TRX-' . $dateString . '-' . strtoupper(Str::random(5));

        return DB::transaction(function () use ($request, $user, $invoiceNumber) {
            
            // 2. Hitung Awal nominal
            $subtotal = 0;
            $itemsToProcess = [];

            // Loop pertama: Validasi ketersediaan seluruh stok bahan baku terlebih dahulu
            foreach ($request->items as $item) {
                $recipe = ProductRecipe::with(['bibit', 'campuran', 'botol'])->find($item['product_recipe_id']);
                $qty = $item['quantity'];

                // Hitung total kebutuhan bahan baku dikali kuantitas barang yang dibeli
                $totalBibitNeeded = $recipe->bibit_volume * $qty;
                $totalCampuranNeeded = $recipe->campuran_volume * $qty;

                // Cek Stok Bibit
                if ($recipe->bibit->stock < $totalBibitNeeded) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Stok bibit {$recipe->bibit->name} tidak mencukupi! Sisa: {$recipe->bibit->stock} ml."
                    ], 422);
                }

                // Cek Stok Cairan Campuran (jika resep memakai campuran)
                if ($recipe->campuran && $recipe->campuran->stock < $totalCampuranNeeded) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Stok cairan campuran {$recipe->campuran->name} tidak mencukupi!"
                    ], 422);
                }

                // Cek Stok Botol Kosong
                if ($recipe->botol->stock < $qty) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Stok {$recipe->botol->name} tidak mencukupi!"
                    ], 422);
                }

                // Hitung subtotal belanjaan
                $itemSubtotal = $recipe->selling_price * $qty;
                $subtotal += $itemSubtotal;

                // Simpan data kalkulasi ke array sementara
                $itemsToProcess[] = [
                    'recipe' => $recipe,
                    'quantity' => $qty,
                    'price' => $recipe->selling_price,
                    'subtotal' => $itemSubtotal,
                    'bibit_decrement' => $totalBibitNeeded,
                    'campuran_decrement' => $totalCampuranNeeded
                ];
            }

            // 3. Simpan Data Induk Transaksi
            $discount = $request->discount;
            $totalAmount = max(0, $subtotal - $discount);

            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $user->id,
                'customer_name' => $request->customer_name,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'status' => 'paid',
            ]);

            // 4. Loop Kedua: Eksekusi Potong Stok & Simpan Detail Item
            foreach ($itemsToProcess as $proc) {
                $recipe = $proc['recipe'];

                // Simpan baris detail nota
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_recipe_id' => $recipe->id,
                    'quantity' => $proc['quantity'],
                    'price' => $proc['price'],
                    'subtotal' => $proc['subtotal'],
                ]);

                // EKSEKUSI POTONG STOK FISIK DI TABEL MATERIALS
                $recipe->bibit->decrement('stock', $proc['bibit_decrement']);
                
                if ($recipe->campuran) {
                    $recipe->campuran->decrement('stock', $proc['campuran_decrement']);
                }
                
                $recipe->botol->decrement('stock', $proc['quantity']);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil diproses, stok otomatis terpotong!',
                'data' => [
                    'invoice_number' => $transaction->invoice_number,
                    'total_amount' => $transaction->total_amount,
                    'payment_method' => $transaction->payment_method
                ]
            ], 201);
        });
    }
}
