<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'nullable|image|max:2048', // Validate image, max 2MB
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'user_id' => auth()->id(),
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Product added successfully!',
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->type,
                'price' => $product->price,
                'stock' => $product->stock,
                'user_id' => $product->user_id,
                'image' => $imagePath ? Storage::url($imagePath) : null,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ]
        ], 201);
    }

    // Fetch only the authenticated user's products
    public function index()
    {
        $products = Product::where('user_id', auth()->id())->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->type,
                'price' => $product->price,
                'stock' => $product->stock,
                'user_id' => $product->user_id,
                'image' => $product->image ? Storage::url($product->image) : null,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];
        });

        return response()->json(['data' => $products]);
    }

    // Update product if owned by user
    public function update(Request $request, $id)
    {
        $product = Product::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:100',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully!',
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->type,
                'price' => $product->price,
                'stock' => $product->stock,
                'user_id' => $product->user_id,
                'image' => $product->image ? Storage::url($product->image) : null,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ]
        ]);
    }

    // Delete product if owned by user
    public function destroy($id)
    {
        $product = Product::where('user_id', auth()->id())->findOrFail($id);

        // Delete associated image if exists
        if ($product->image) {
            Storage::delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}