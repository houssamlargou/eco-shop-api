<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/profile',[AuthController::class, 'profile'])->middleware('auth:sanctum');
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store'])->middleware(['auth:sanctum','admin']);
Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware(['auth:sanctum','admin']);
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware(['auth:sanctum', 'admin']);
Route::post('/products', [ProductController::class, 'store'])->middleware(['auth:sanctum','admin']);
Route::get('/products',[ProductController::class,'index']);
Route::get('/products/{product}', [ProductController::class,'show']); 
Route::put('/products/{product}',[ProductController::class,'update'])->middleware(['auth:sanctum','admin']);
Route::delete('/products/{product}',[ProductController::class,'destroy'])->middleware(['auth:sanctum','admin']);
Route::post('/cart/items',[CartController::class,'addItem'])->middleware('auth:sanctum');
Route::get('/cart',[CartController::class,'show'])->middleware('auth:sanctum');
Route::put('/cart/items/{cartItem}',[CartController::class,'updateItemQuantity'])->middleware('auth:sanctum');
Route::delete('/cart/items/{cartItem}',[CartController::class,'removeItem'])->middleware('auth:sanctum');
Route::post('/checkout',[OrderController::class,'checkout'])->middleware('auth:sanctum');
Route::get('/orders',[OrderController::class,'index'])->middleware('auth:sanctum'); 
Route::get('/orders/{order}',[OrderController::class,'show'])->middleware('auth:sanctum');
Route::put('/orders/{order}/status',[OrderController::class,'updateStatus'])->middleware(['auth:sanctum','admin']);