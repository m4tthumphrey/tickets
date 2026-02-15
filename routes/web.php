<?php

use App\Http\Controllers\Api\AccessCodeController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/api/access-code/validate', [AccessCodeController::class, 'validate']);
Route::get('/api/access-code/status', [AccessCodeController::class, 'status']);
Route::post('/api/access-code/logout', [AccessCodeController::class, 'logout']);

Route::get('/api/products', [ProductController::class, 'index']);
Route::get('/api/filters', [ProductController::class, 'filters']);

Route::get('/{any?}', fn () => view('app'))->where('any', '.*');
