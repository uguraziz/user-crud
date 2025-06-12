<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\User;
use Spatie\Permission\Models\Role;
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
        // Artık 2FA response'u bekliyoruz
        $response->assertJsonStructure([
            'message',
            'requires_2fa'
        ]);
        $response->assertJson([
            'message' => 'Verification code sent to your email.',
            'requires_2fa' => true
        ]);

        // 2FA kodu veritabanında oluşturulmuş mu kontrol et
        $user->refresh();
        $this->assertNotNull($user->two_factor_code);
        $this->assertNotNull($user->two_factor_expires_at);
    }

    public function testTwoFactorVerification()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123')
        ]);

        // Önce login yap (2FA kodu oluşturmak için)
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);

        $loginResponse->assertStatus(200);

        // User'ı refresh et ve 2FA kodunu al
        $user->refresh();
        $twoFactorCode = $user->two_factor_code;

        // 2FA doğrulaması yap
        $verifyResponse = $this->postJson('/api/verify-2fa', [
            'code' => $twoFactorCode
        ]);

        $verifyResponse->assertStatus(200);
        $verifyResponse->assertJsonStructure([
            'message',
            'token',
            'user'
        ]);
        $verifyResponse->assertJson([
            'message' => 'Login successful'
        ]);

        // 2FA kodu temizlenmiş mi kontrol et
        $user->refresh();
        $this->assertNull($user->two_factor_code);
        $this->assertNull($user->two_factor_expires_at);
    }

    public function testTwoFactorVerificationWithInvalidCode()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123')
        ]);

        // Önce login yap
        $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);

        // Yanlış kod ile doğrulama dene
        $verifyResponse = $this->postJson('/api/verify-2fa', [
            'code' => '999999'
        ]);

        $verifyResponse->assertStatus(400);
        $verifyResponse->assertJson([
            'message' => 'Invalid or expired verification code.'
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
        $response->assertJsonValidationErrors(['email']);
        $response->assertJsonFragment([
            'email' => ['The provided credentials are incorrect.']
        ]);
        $response->assertJsonMissing(['token', 'user', 'requires_2fa']);
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

        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'full_name',
                'email',
                'roles',
                'permissions'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com'
            ]
        ]);
    }

    public function testUserWithoutAuthentication()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
}
