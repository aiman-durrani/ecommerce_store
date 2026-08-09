<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_same_product_twice_increments_quantity(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create([
            'stock' => 50,
            'is_active' => true,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.product_id', $product->id)
            ->assertJsonPath('data.items.0.quantity', 4);
    }

    public function test_adding_more_than_available_stock_is_rejected(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create([
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 10,
            ]);

        $response->assertStatus(422);
    }

    public function test_checkout_creates_order_and_decrements_stock(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create([
            'price' => 50.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 3,
            ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout', [
                'shipping_address' => '123 Main Street',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 7,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 150.00,
            'shipping_address' => '123 Main Street',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 50.00,
        ]);

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertEquals(0, $cart->cartItems()->count());
    }

    public function test_checkout_with_insufficient_stock_fails_and_rolls_back(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create([
            'stock' => 2,
            'is_active' => true,
        ]);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Reduce product's stock to 1 in DB
        $product->update(['stock' => 1]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout', [
                'shipping_address' => '123 Main Street',
            ]);

        $response->assertStatus(422);

        $this->assertEquals(0, Order::count());

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 1,
        ]);
    }

    public function test_checkout_with_empty_cart_fails(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout', [
                'shipping_address' => '123 Main Street',
            ]);

        $response->assertStatus(422);
    }
}
