<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    public function test_admin_can_list_all_orders(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'status', 'total', 'shipping_address', 'items', 'created_at', 'updated_at'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_admin_can_filter_orders_by_status(): void
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'shipped']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/orders?status=shipped');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'shipped');
    }

    public function test_customer_cannot_list_all_orders(): void
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/admin/orders');

        $response->assertStatus(403);
    }

    public function test_admin_can_update_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$order->id}/status", [
                'status' => 'shipped',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'shipped');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);
    }

    public function test_update_status_fails_with_invalid_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$order->id}/status", [
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_customer_cannot_update_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->patchJson("/api/admin/orders/{$order->id}/status", [
                'status' => 'shipped',
            ]);

        $response->assertStatus(403);
    }
}
