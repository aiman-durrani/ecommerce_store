<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create all 12 categories
        $categories = Category::factory()->count(12)->create();

        // 2. For each created category, create 4 products
        foreach ($categories as $category) {
            Product::factory()->count(4)->forCategory($category)->create();
        }

        $allProducts = Product::all();

        // 3. Create 10 users
        $users = User::factory()->count(10)->create();

        // 4. For 5 of those users, create a cart with 2-4 cart items each using random existing products
        $cartUsers = $users->random(5);
        foreach ($cartUsers as $user) {
            $cart = Cart::factory()->create([
                'user_id' => $user->id,
            ]);

            $selectedProducts = $allProducts->random(rand(2, 4));
            foreach ($selectedProducts as $product) {
                CartItem::factory()->create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                ]);
            }
        }

        // 5. For 6 of those users, create 1-2 orders each, with 2-3 order items each and price copied from product
        $orderUsers = $users->random(6);
        foreach ($orderUsers as $user) {
            $numOrders = rand(1, 2);
            for ($i = 0; $i < $numOrders; $i++) {
                $selectedProducts = $allProducts->random(rand(2, 3));

                $orderTotal = 0;
                $itemsToCreate = [];
                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 4);
                    $price = $product->price;
                    $orderTotal += $price * $quantity;
                    $itemsToCreate[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                    ];
                }

                $order = Order::factory()->create([
                    'user_id' => $user->id,
                    'total' => $orderTotal,
                ]);

                foreach ($itemsToCreate as $itemData) {
                    OrderItem::factory()->create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                    ]);
                }
            }
        }

        // 6. Create 15 reviews scattered across random products and users, ratings 1-5
        for ($i = 0; $i < 15; $i++) {
            Review::factory()->create([
                'user_id' => $users->random()->id,
                'product_id' => $allProducts->random()->id,
                'rating' => rand(1, 5),
            ]);
        }
    }
}

