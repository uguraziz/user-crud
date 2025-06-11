<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function user(Request $request)
    {
        return $request->user();
    }
    public function register(RegisterRequest $request)
    {
        $register = $request->validated();

        $user = User::create($register);

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

        if(! $user || !Hash::check($request->password, $user->password) === false) {
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
        $request->user()->tokens()->delete();

        return [
            'message' => 'Logout successful',
        ];
    }
}
