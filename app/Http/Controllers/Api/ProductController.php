<?php

namespace App\Http\Controllers\Api;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(): JsonResponse {
        $products = Product::with('category')
                         ->where('is_active', true)
                         ->latest()
                         ->get();
                    
        return response()->json([
            'products' => $products,
        ],200);
    }

    public function show(Product $product): JsonResponse {
        if(!$product->is_active){
            throw new NotFoundHttpException('Product not found.');
        }

        $product->load('category');

        return response()->json([
            'product' => $product,
        ],200);
    }

    public function store(Request $request): JsonResponse {
        $validated = $request->validate([
            'category_id' => ['required','integer','exists:categories,id'],
            'name' => ['required','string','max:255',"unique:products,name"],
            'description' => ['nullable','string'],
            'price' => ['required','numeric','min:0'],
            'stock' => ['required','integer','min:0'],
            'image_url' => ['nullable','string','max:255'],
            'is_active' => ['nullable','boolean'],
        ]);

        $slug = Str::slug($validated['name']);
        $product = Product::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image_url' => $validated['image_url'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $product
        ], 201);
    }

    public function update(Request $request, Product $product): JsonResponse {
        $validated = $request->validate([
            'category_id' => ['required','integer','exists:categories,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products','name')->ignore($product->id),
            ],
            'description' => ['nullable','string'],
            'price' => ['required','numeric','min:0'],
            'stock' => ['required','integer','min:0'],
            'image_url' => ['nullable','max:255'],
            'is_active' => ['nullable','boolean'],
        ]);

        $slug = Str::slug($validated['name']);

        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image_url' => $validated['image_url'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => $product->load('category'),
        ],200);
    }
}
