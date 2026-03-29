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

    public function updateItemQuantity(Request $request, CartItem $cartItem): JsonResponse {
        $validate = $request->validate([
            'quantity' => ['required','integer','min:1'],
        ]);

        $user = $request->user();

        if($cartItem->cart->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are not allowed to update this cart item.'
            ],403);
        }

        if($validate['quantity'] > $cartItem->product->stock) {
            return response()->json([
                'message' => 'Requested quantity exceeds availble stock',
            ],422);
        }

        $cartItem->update([
            'quantity' => $validate['quantity'],
        ]);

        return response()->json([
            'message' => 'Cart item quantity updated successfully.',
            'cart_item' => $cartItem->load('product')
        ],200);
    }

    public function removeItem(Request $request, CartItem $cartItem): JsonResponse {
        $user = $request->user();

        if($cartItem->cart->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are nor allowed to remove this cart item.',
            ],403);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Cart item removed successfully.'
        ],200);
    }
}
