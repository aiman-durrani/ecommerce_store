<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->forCategory($category)->create([
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_guest_can_view_single_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create([
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $product->id);
    }

    public function test_customer_cannot_create_product(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'name' => 'Sample Product',
            'price' => 29.99,
            'stock' => 10,
        ];

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/products', $payload);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_update_product(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        $response = $this->actingAs($customer, 'sanctum')
            ->putJson("/api/products/{$product->id}", [
                'name' => 'Unauthorized Update',
            ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_delete_product(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        $response = $this->actingAs($customer, 'sanctum')
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'name' => 'New Admin Product',
            'description' => 'A valid product description.',
            'price' => 99.99,
            'stock' => 50,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/products', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'name' => 'New Admin Product',
        ]);
    }

    public function test_admin_can_update_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create([
            'name' => 'Old Name',
            'price' => 10.00,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/products/{$product->id}", [
                'name' => 'Updated Product Name',
                'price' => 20.00,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 20.00,
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_creating_product_with_invalid_data_fails_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'price' => -10.00,
            'stock' => 5,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/products', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price']);
    }
}
