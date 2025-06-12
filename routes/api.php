<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post("register", [AuthController::class, 'register']);
Route::post("login", [AuthController::class, 'login']);
Route::post('/verify-2fa', [AuthController::class, 'verifyTwoFactor']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get("user", [AuthController::class, 'user']);
    Route::post("logout", [AuthController::class, 'logout']);
});
