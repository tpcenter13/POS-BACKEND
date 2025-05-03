<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Store product for authenticated user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Product added successfully!',
            'data' => $product
        ], 201);
    }

    // Fetch only the authenticated user's products
    public function index()
    {
        $products = Product::where('user_id', auth()->id())->get();

        return response()->json(['data' => $products]);
    }

    // Update product if owned by user
    public function update(Request $request, $id)
    {
        $product = Product::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'type' => 'string|max:100',
            'price' => 'numeric|min:0',
            'stock' => 'integer|min:0',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully!',
            'data' => $product
        ]);
    }

    // Delete product if owned by user
    public function destroy($id)
    {
        $product = Product::where('user_id', auth()->id())->findOrFail($id);

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
