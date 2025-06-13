<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Controllers\UserController;
use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase
{
    public function testIndex()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        User::factory()->count(5)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'country',
                ]
            ],
        ]);
    }

    public function testIndexWithSearch()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ]);

        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users?filter[first_name]=john');

        $response->assertStatus(200);
        $response->assertJsonFragment(['first_name' => 'John']);
    }

    public function testIndexPagination()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        User::factory()->count(25)->create();

        $response = $this->getJson('/api/users?per_page=10&page=2');

        $response->assertStatus(200);
    }

    public function testShow()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        $user = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com'
        ]);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'full_name',
                'email',
                'country',
                'city'
            ]
        ]);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane@example.com'
            ]
        ]);
    }

    public function testShowNotFound()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/users/999');

        $response->assertStatus(404);
    }

    public function testStore()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        $userData = [
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'newuser@example.com',
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

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'first_name',
                'last_name',
                'email'
            ]
        ]);
        $response->assertJson([
            'message' => 'User created successfully'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'first_name' => 'New',
            'last_name' => 'User'
        ]);
    }

    public function testStoreValidationErrors()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/users', [
            'first_name' => '', // Required field boş
            'email' => 'invalid-email', // Geçersiz email
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name', 'email', 'password']);
    }

    public function testUpdate()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        $user = User::factory()->create([
            'first_name' => 'Old',
            'last_name' => 'Name',
            'email' => 'old@example.com'
        ]);

        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'city' => 'Ankara'
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'first_name',
                'last_name',
                'email'
            ]
        ]);
        $response->assertJson([
            'message' => 'User updated successfully'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
            'city' => 'Ankara'
        ]);
    }

    public function testUpdateWithPassword()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        $user = User::factory()->create();
        $oldPassword = $user->password;

        $response = $this->putJson("/api/users/{$user->id}", [
            'password' => 'newpassword123'
        ]);

        $response->assertStatus(200);

        $user->refresh();
        // Password değişmiş olmalı (hash'lenmiş)
        $this->assertNotEquals($oldPassword, $user->password);
    }

    public function testUpdateUniqueValidation()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // user2'yi user1'in email'i ile güncellemek istiyoruz (hata vermeli)
        $response = $this->putJson("/api/users/{$user2->id}", [
            'email' => 'user1@example.com'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function testDestroy()
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::ADMIN->value);
        $this->actingAs($admin, 'sanctum');

        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => "User deleted successfully"
        ]);

        $this->assertSoftDeleted('users', [
            'id' => $user->id
        ]);
    }

    public function testDestroyNotFound()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'sanctum');

        $response = $this->deleteJson('/api/users/999');

        $response->assertStatus(404);
    }

    public function testUnauthorizedAccess()
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);

        $response = $this->postJson('/api/users', []);
        $response->assertStatus(401);
    }
}
