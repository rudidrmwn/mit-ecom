<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Get cart by user ID
     */
    public function getUserCart($userId)
    {
        try {
            $cart = Cart::with([
                'cartItems.product'            
            ])
            ->where('id_user', $userId)
            ->first();

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $cart
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or update existing cart for user
     */
    public function createOrUpdateCart(Request $request)
    {
        try {
           
            // Create atau update cart berdasarkan user
            $cart = Cart::updateOrCreate(
                ['id_user' => $request->iduser]
            );

            // Loop melalui setiap produk
            foreach ($request->input('products') as $product) {
                // Cek apakah ada attributes
                if (!empty($product['attributes'])) {
                    // Loop melalui setiap attribute dari produk
                    foreach ($product['attributes'] as $attribute) {
                        $cartItem = CartItem::updateOrCreate(
                            [
                                'id_cart' => $cart->id,
                                'id_product' => $product['idproduct'],
                                'id_product_attribute' => $attribute['itemId']
                            ],
                            [
                                'product_name' => $product['productname'],
                                'product_attribute_name' => $attribute['itemName'],
                                'qty' => $attribute['qty']
                            ]
                        );
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => $cart->wasRecentlyCreated ? 'Cart created successfully' : 'Cart updated successfully',
                'data' => $cart
            ], $cart->wasRecentlyCreated ? 201 : 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create or update cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete product from cart
     */
    public function clearItemCart($cartId, $itemId)
    {
        try {
            $cart = Cart::find($cartId);

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found'
                ], 404);
            }
           
            CartItem::where('id', $itemId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'item {$itemId} cleared successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete cart
     */
    public function deleteCart($cartId)
    {
        try {
            $cart = Cart::find($cartId);

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found'
                ], 404);
            }

            // Delete all cart items first
            CartItem::where('id_cart', $cartId)->delete();
            
            // Delete cart
            $cart->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}