<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/profile',[AuthController::class, 'profile'])->middleware('auth:sanctum');
Route::get('/admin-test', function() {
    return response()->json([
        'message' => 'Welcome admin.',
    ]);
})->middleware(['auth:sanctum', 'admin']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store'])->middleware(['auth:sanctum','admin']);
Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware(['auth:sanctum','admin']);
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware(['auth:sanctum', 'admin']);
Route::post('/products', [ProductController::class, 'store'])->middleware(['auth:sanctum','admin']);
Route::get('/products',[ProductController::class,'index']);
Route::get('/products/{product}', [ProductController::class,'show']); 


