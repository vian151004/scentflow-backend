<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $materials = Material::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'List Data Materials',
            'data'    => $materials
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'               => 'required|string|unique:materials,sku',
            'name'              => 'required|string|max:255',
            'category'          => 'required|in:bibit,botol,campuran',
            'stock'             => 'required|numeric|min:0',
            'unit'              => 'required|string|max:50',
            'threshold_minimum' => 'nullable|numeric|min:0',
        ]);

        $material = Material::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Material created successfully',
            'data'    => $material
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Material details',
            'data'    => $material
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found',
            ], 404);
        }

        $validated = $request->validate([
            'sku'               => 'sometimes|required|string|unique:materials,sku,' . $material->id,
            'name'              => 'sometimes|required|string|max:255',
            'category'          => 'sometimes|required|in:bibit,botol,campuran',
            'stock'             => 'sometimes|required|numeric|min:0',
            'unit'              => 'sometimes|required|string|max:50',
            'threshold_minimum' => 'nullable|numeric|min:0',
        ]);

        $material->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Material updated successfully',
            'data'    => $material
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found',
            ], 404);
        }

        $material->delete();

        return response()->json([
            'success' => true,
            'message' => 'Material deleted successfully',
        ], 200);
    }
}
