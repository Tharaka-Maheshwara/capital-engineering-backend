<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_with_user_role_and_returns_token(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Jane Builder',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.name', 'Jane Builder')
            ->assertJsonPath('data.user.email', 'jane@example.com')
            ->assertJsonPath('data.user.role', 'user');

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'user',
        ]);

        $user = User::where('email', 'jane@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotNull($user->api_token_hash);
        $this->assertNotSame($user->api_token_hash, $response->json('data.token'));
    }

    public function test_login_returns_token_for_valid_credentials(): void
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'existing@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'existing@example.com')
            ->assertJsonPath('data.user.role', 'user')
            ->assertJsonStructure(['message', 'data' => ['user', 'token']]);
    }
}