<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Display the authenticated user's cart.
     */
    public function index(Request $request): CartResource
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cart->load('cartItems.product');

        return new CartResource($cart);
    }

    /**
     * Add a product to the cart or update quantity if already exists.
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->product_id);
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $cartItem = $cart->cartItems()->where('product_id', $product->id)->first();
        $requestedQuantity = (int) $request->quantity;
        $newQuantity = $cartItem ? ($cartItem->quantity + $requestedQuantity) : $requestedQuantity;

        if ($product->stock < $newQuantity) {
            throw ValidationException::withMessages([
                'quantity' => ['Insufficient stock available for this product.'],
            ]);
        }

        if ($cartItem) {
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            $cartItem = $cart->cartItems()->create([
                'product_id' => $product->id,
                'quantity' => $requestedQuantity,
            ]);
        }

        $cartItem->load('product');

        return response()->json(new CartItemResource($cartItem), 201);
    }

    /**
     * Update the specified cart item's quantity.
     */
    public function update(UpdateCartItemRequest $request, CartItem $item): JsonResponse
    {
        // Enforce ownership
        if ($item->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product = $item->product;
        $newQuantity = (int) $request->quantity;

        if ($product->stock < $newQuantity) {
            throw ValidationException::withMessages([
                'quantity' => ['Insufficient stock available for this product.'],
            ]);
        }

        $item->update(['quantity' => $newQuantity]);
        $item->load('product');

        return response()->json(new CartItemResource($item));
    }

    /**
     * Remove the specified item from the cart.
     */
    public function destroy(Request $request, CartItem $item): JsonResponse
    {
        // Enforce ownership
        if ($item->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Cart item deleted successfully']);
    }
}
