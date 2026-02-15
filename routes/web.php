<?php

use App\Http\Controllers\Api\AccessCodeController;
use App\Http\Controllers\Api\AdminAccessCodeController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Middleware\LogAccessCodeActivity;
use Illuminate\Support\Facades\Route;

Route::post('/api/access-code/validate', [AccessCodeController::class, 'validate']);
Route::get('/api/access-code/status', [AccessCodeController::class, 'status']);
Route::post('/api/access-code/logout', [AccessCodeController::class, 'logout']);

Route::middleware(LogAccessCodeActivity::class)->group(function () {
    Route::get('/api/products', [ProductController::class, 'index']);
    Route::get('/api/products/{product}', [ProductController::class, 'show']);
});

Route::get('/api/filters', [ProductController::class, 'filters']);

// Admin auth (no middleware)
Route::post('/api/admin/login', [AdminAuthController::class, 'login']);
Route::get('/api/admin/status', [AdminAuthController::class, 'status']);

// Admin API (requires auth)
Route::middleware('auth')->prefix('api/admin')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::get('/access-codes', [AdminAccessCodeController::class, 'index']);
    Route::post('/access-codes', [AdminAccessCodeController::class, 'store']);
    Route::get('/access-codes/{accessCode}', [AdminAccessCodeController::class, 'show']);
    Route::put('/access-codes/{accessCode}', [AdminAccessCodeController::class, 'update']);
    Route::post('/access-codes/{accessCode}/revoke', [AdminAccessCodeController::class, 'revoke']);
    Route::delete('/access-codes/{accessCode}', [AdminAccessCodeController::class, 'destroy']);
    Route::get('/filters', [AdminAccessCodeController::class, 'filters']);
});

Route::get('/{any?}', fn () => view('app'))->where('any', '.*');
