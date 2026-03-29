<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout(Request $request): JsonResponse {
        $user = $request->user();

        $cart = Cart::with(['items.product'])
                    ->where('user_id',$user->id)
                    ->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Your cart is empty.',
            ],422);
        }

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (!$product) {
                return response()->json([
                    'message' => 'A product in your cart no longer exists.',
                ],422);
            }

            if (!$product->is_active) {
                return response()->json([
                    'message' => "Product '{$product->name}' is not available."
                ],422);
            }

            if ($item->quantity > $product->stock) {
                return response()->json([
                    'message' => "Requested quantity for '{$product->name}' exceeds available stock.",
                ],422);
            }
        }

        $order = DB::transaction(function() use ($user,$cart){
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => 0,
            ]);

            $total = 0;

            foreach($cart->items as $item) {
                $product = $item->product;
                $price = (float) $product->price;
                $subtotal = round($price * $item->quantity, 2);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $price,
                    'quantity' => $item->quantity,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $order->update([
                'total' => round($total,2),
            ]);

            $cart->items()->delete();

            return $order->load('items');
        });

        return response()->json([
            'message' => 'Checkout completed successfully.',
            'order' => $order,
        ],201);
    }
}
