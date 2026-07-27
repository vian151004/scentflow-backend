<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar varian produk Scentflow',
            'data' => ProductResource::collection($products)
        ]);   
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'base_price_per_ml' => 'required|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
        ]);

        // generate slug
        $validated['slug'] = Str::slug($request->name) . '-' . Str::random(5);

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Varian produk berhasil ditambahkan',
            'data' => new ProductResource($product)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['recipes.fragrance', 'recipes.mixture', 'recipes.bottle'])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail varian produk Scentflow',
            'data' => new ProductResource($product)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:255',
            'base_price_per_ml' => 'sometimes|required|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $id,
            'description' => 'nullable|string',
            'is_available' => 'sometimes|boolean',
        ]);

        // generate slug if name is updated
        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($request->name) . '-' . Str::random(5);
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Varian produk berhasil diperbarui',
            'data' => new ProductResource($product)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Varian produk berhasil dihapus',
        ], 200);
    }
}
