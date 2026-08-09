<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    /**
     * Create an order from the user's cart inside a database transaction.
     *
     * @param User $user
     * @param array $shippingData
     * @return Order
     * @throws InvalidArgumentException
     */
    public function createFromCart(User $user, array $shippingData): Order
    {
        return DB::transaction(function () use ($user, $shippingData) {
            $cart = Cart::where('user_id', $user->id)->first();

            if (!$cart || $cart->cartItems()->count() === 0) {
                throw new InvalidArgumentException('Cart is empty.');
            }

            $cartItems = $cart->cartItems()->get();
            $orderItemsData = [];
            $total = 0;

            foreach ($cartItems as $item) {
                // Lock product row for update to prevent race conditions during checkout
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                if (!$product || $product->stock < $item->quantity) {
                    throw new InvalidArgumentException(
                        "Insufficient stock for product: " . ($product ? $product->name : 'Unknown')
                    );
                }

                $itemSubtotal = $product->price * $item->quantity;
                $total += $itemSubtotal;

                $orderItemsData[] = [
                    'product' => $product,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                ];
            }

            // Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => round($total, 2),
                'shipping_address' => $shippingData['shipping_address'],
            ]);

            // Create OrderItems and decrement stock
            foreach ($orderItemsData as $data) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $data['product']->id,
                    'quantity' => $data['quantity'],
                    'price' => $data['price'],
                ]);

                $data['product']->decrement('stock', $data['quantity']);
            }

            // Clear cart items
            $cart->cartItems()->delete();

            return $order->load('orderItems.product');
        });
    }
}
