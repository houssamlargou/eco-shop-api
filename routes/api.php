<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/profile',[AuthController::class, 'profile'])->middleware('auth:sanctum');
Route::get('/admin-test', function() {
    return response()->json([
        'message' => 'Welcome admin.',
    ]);
})->middleware(['auth:sanctum', 'admin']);
Route::post('/categories', [CategoryController::class, 'store'])->middleware(['auth:sanctum','admin']);
