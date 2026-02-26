<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes - Rate limited
Route::middleware('throttle:api')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// Protected routes - Requires JWT authentication and rate limiting
// All routes in this group require a valid JWT token in Authorization header
// Header format: Authorization: Bearer {token}
// Rate limit: 60 requests per minute per IP address
Route::middleware(['throttle:api', 'auth:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Product CRUD routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::patch('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});
