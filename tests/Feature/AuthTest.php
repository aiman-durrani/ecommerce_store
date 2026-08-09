<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_a_token(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token']);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
        ]);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_me_endpoint_requires_authentication(): void
    {
        // Unauthenticated request
        $unauthenticatedResponse = $this->getJson('/api/me');
        $unauthenticatedResponse->assertStatus(401);

        // Authenticated request
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $authenticatedResponse = $this->getJson('/api/me');
        $authenticatedResponse->assertStatus(200)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_logout_revokes_the_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        // Logout using the valid token
        $logoutResponse = $this->postJson('/api/logout', [], $headers);
        $logoutResponse->assertStatus(200);

        // Request /api/me again with the same revoked token
        $meResponse = $this->getJson('/api/me', $headers);
        $meResponse->assertStatus(401);
    }
}
