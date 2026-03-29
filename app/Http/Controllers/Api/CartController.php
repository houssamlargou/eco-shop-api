<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addItem(Request $request): JsonResponse {
        $validated = $request->validate([
            'product_id' => ['required','integer','exists:products,id'],
            'quantity' => ['nullable','integer','min:1'],
        ]);

        $user = $request->user();

        $cart = Cart::firstOrCreate([
            'user_id' => $user->id,
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if(!$product->is_active) {
            return response()->json([
                'message' => 'This product is not available.'
            ],422);
        }

        $quantityToAdd = $validated['quantity'] ?? 1;

        $cartItem = CartItem::where('cart_id',$cart->id)->where('product_id',$product->id)->first();

        if($cartItem) {
            $cartItem->increment('quantity',$quantityToAdd);
            $cartItem->refresh();
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantityToAdd
            ]);
        }

        return response()->json([
            'message' => 'Product added to cart successfully.',
            'cart_item' => $cartItem->load('product'),
        ],201);
    }

    public function show(Request $request): JsonResponse {
        $user = $request->user();

        $cart = Cart::with(['items.product'])->where('user_id',$user->id)->first();

        if(!$cart) {
            return response()->json([
                'message' => 'Cart is empty.',
                'cart' => null,
                'items' => [],
            ],200);
        }

        return response()->json([
            'message' => 'Cart retrieved successfully.',
            'cart' => $cart,
        ],200);
    }
}
