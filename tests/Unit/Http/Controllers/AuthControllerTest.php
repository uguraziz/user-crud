<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\User;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function testRegister()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'national_id' => '12345678901',
            'country' => 'Turkey',
            'city' => 'Istanbul',
            'district' => 'Kadikoy',
            'currency' => 'TRY',
            'phone' => '+905551234567',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => '123 Test Street',
            'postal_code' => '34000'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'token',
            'user'
        ]);
        $response->assertJson([
            'message' => 'Registration successful'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
    }

    public function testLogin()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'token',
            'user'
        ]);
        $response->assertJson([
            'message' => 'Login successful'
        ]);
    }

    public function testLoginWithInvalidCredentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'The provided credentials are incorrect.'
        ]);
        $response->assertJsonMissing(['token', 'user']);
    }

    public function testLoginWithNonExistentUser()
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function testLogout()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Logout successful'
        ]);
    }

    public function testLogoutWithoutToken()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    public function testUser()
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ]);
    }

    public function testUserWithoutAuthentication()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
}
