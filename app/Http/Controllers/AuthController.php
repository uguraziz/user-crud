<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;


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
            return [
                'message' => 'The provided credentials are incorrect.',
            ];
        }

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
