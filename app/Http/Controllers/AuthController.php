<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class AuthController extends Controller
{
    private const CACHE_DURATION = 60 * 60; // 1 Hours

    public function user(Request $request)
    {
        $userId = $request->user()->id;

        $user = Cache::remember('user_profile_' . $userId, self::CACHE_DURATION, function () use ($userId) {
            return new UserResource(User::find($userId));
        });

        return $user;
    }

    public function register(RegisterRequest $request)
    {
        $register = $request->validated();

        $user = User::create($register)->assignRole(RoleEnum::EDITOR);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'message' => 'Registration successful',
            'token' => $token,
            'user' => $user
        ];
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|max:255|email|exists:users',
            'password' => 'required|string',
        ]);
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::warning('Failed login attempt', ['email' => $request->email]);
            return [
                'errors' => [
                    'email' => ['The provided credentials are incorrect.']
                ]
            ];
        }

        $code = $user->generateTwoFactorCode();
         Mail::raw("Your verification code is: {$code}", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Login Verification Code');
        });

        Log::info('2FA code sent', ['code' => $code]);

        return [
            'message' => 'Verification code sent to your email.',
            'requires_2fa' => true
        ];
    }

    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('two_factor_code', $request->code)
                    ->where('two_factor_expires_at', '>', now())
                    ->first();

        if (!$user) {
            Log::warning('Invalid or expired 2FA code attempt', ['code' => $request->code]);
            return response()->json([
                'message' => 'Invalid or expired verification code.',
            ], 400);
        }

        $user->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        Log::info('2FA successful', ['user_id' => $user->id]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ];
    }

    public function logout(Request $request)
    {
        $userId = $request->user()->id;

        $request->user()->tokens()->delete();

        Cache::forget('user_profile_' . $userId);

        return [
            'message' => 'Logout successful',
        ];
    }
}
