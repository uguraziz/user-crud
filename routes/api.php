<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest','throttle:10,1'])->group(function () {
    Route::post("register", [AuthController::class, 'register']);
    Route::post("login", [AuthController::class, 'login']);
    Route::post('verify-2fa', [AuthController::class, 'verifyTwoFactor']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get("user", [AuthController::class, 'user']);
    Route::post("logout", [AuthController::class, 'logout']);

    Route::ApiResource('users', UserController::class);
});
