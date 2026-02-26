<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes - Requires JWT authentication
// All routes in this group require a valid JWT token in Authorization header
// Header format: Authorization: Bearer {token}
Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Product routes will be added here
});
