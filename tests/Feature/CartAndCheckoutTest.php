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

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $category = Category::factory()->create(['name' => 'Electronics']);
        $this->product = Product::factory()->forCategory($category)->create([
            'price' => 50.00,
            'stock' => 10,
            'is_active' => true,
        ]);
    }

    public function test_authenticated_user_can_view_cart(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'items',
                    'total',
                ],
            ]);
    }

    public function test_user_can_add_item_to_cart(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cart/items', [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'subtotal' => 100.00,
                ],
            ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_adding_existing_product_increments_quantity(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cart/items', [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cart/items', [
                'product_id' => $this->product->id,
                'quantity' => 3,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'subtotal' => 250.00,
                ],
            ]);
    }

    public function test_cannot_add_item_with_quantity_exceeding_stock(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cart/items', [
                'product_id' => $this->product->id,
                'quantity' => 15, // stock is 10
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_user_can_update_cart_item_quantity(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/cart/items/{$cartItem->id}", [
                'quantity' => 4,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $cartItem->id,
                    'quantity' => 4,
                    'subtotal' => 200.00,
                ],
            ]);
    }

    public function test_updating_cart_item_fails_if_quantity_exceeds_stock(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/cart/items/{$cartItem->id}", [
                'quantity' => 20, // stock is 10
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_update_another_users_cart_item(): void
    {
        $otherUser = User::factory()->create();
        $otherCart = Cart::factory()->create(['user_id' => $otherUser->id]);
        $otherItem = CartItem::factory()->create([
            'cart_id' => $otherCart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/cart/items/{$otherItem->id}", [
                'quantity' => 2,
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_cart_item(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/cart/items/{$cartItem->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_cannot_delete_another_users_cart_item(): void
    {
        $otherUser = User::factory()->create();
        $otherCart = Cart::factory()->create(['user_id' => $otherUser->id]);
        $otherItem = CartItem::factory()->create([
            'cart_id' => $otherCart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/cart/items/{$otherItem->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_checkout_successfully(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/checkout', [
                'shipping_address' => '123 Main Street, City',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'status' => 'pending',
                    'total' => 100.00,
                    'shipping_address' => '123 Main Street, City',
                    'items' => [
                        [
                            'product_id' => $this->product->id,
                            'quantity' => 2,
                            'price' => 50.00,
                            'subtotal' => 100.00,
                        ],
                    ],
                ],
            ]);

        // Verify stock decremented (10 - 2 = 8)
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock' => 8,
        ]);

        // Verify cart emptied
        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
        ]);
    }

    public function test_checkout_fails_when_cart_is_empty(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/checkout', [
                'shipping_address' => '123 Main Street, City',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cart is empty.',
            ]);
    }

    public function test_checkout_fails_if_stock_becomes_insufficient(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);

        // Reduce product stock to 5 before checkout
        $this->product->update(['stock' => 5]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/checkout', [
                'shipping_address' => '123 Main Street, City',
            ]);

        $response->assertStatus(422);
    }

    public function test_user_can_list_their_orders(): void
    {
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'total' => 150.00,
            'shipping_address' => 'Address 1',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_view_single_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'total' => 150.00,
            'shipping_address' => 'Address 1',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'total' => 150.00,
                ],
            ]);
    }

    public function test_cannot_view_another_users_order(): void
    {
        $otherUser = User::factory()->create();
        $otherOrder = Order::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'pending',
            'total' => 150.00,
            'shipping_address' => 'Address 2',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders/{$otherOrder->id}");

        $response->assertStatus(403);
    }
}
