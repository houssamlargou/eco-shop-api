<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse {
        $categories = Category::latest()->get();

        return response()->json([
            'categories' => $categories,
        ], 200);
    }

    public function store(Request $request): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);


        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category->fresh(),
        ], 201);
    }

    public function update(Request $request,Category $category): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $category
        ], 200);
    }

    public function destroy(Category $category): JsonResponse {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ], 200);
    }
}
