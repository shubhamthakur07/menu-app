<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\Owner\CategoryController;
use App\Http\Controllers\Owner\MenuItemController;
use App\Http\Controllers\Owner\OwnerOrderController;
use App\Http\Controllers\Owner\TableController;
use App\Http\Controllers\Owner\RestaurantController;
use App\Http\Controllers\PublicMenuController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => response()->json(['status' => 'ok', 'time' => time()]));

/*
|--------------------------------------------------------------------------
| Public Customer Routes
|--------------------------------------------------------------------------
*/
Route::get('/restaurants', [PublicMenuController::class, 'list']);
Route::get('/r/{slug}', [PublicMenuController::class, 'show']);
Route::get('/r/{slug}/slots', [PublicMenuController::class, 'getSlots']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{orderCode}', [OrderController::class, 'show']);
Route::post('/orders/{orderCode}/verify-payment', [OrderController::class, 'recordPayment']);
Route::post('/orders/{orderCode}/apply-game-reward', [OrderController::class, 'applyGameDiscount']);

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/user', [AuthController::class, 'user']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

Route::post('/upload', [UploadController::class, 'upload']);

/*
|--------------------------------------------------------------------------
| Owner Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Restaurant Profile & Analytics
    Route::get('/owner/restaurant', [RestaurantController::class, 'show']);
    Route::put('/owner/restaurant', [RestaurantController::class, 'update']);

    // Categories
    Route::get('/owner/categories', [CategoryController::class, 'index']);
    Route::post('/owner/categories', [CategoryController::class, 'store']);
    Route::put('/owner/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/owner/categories/{id}', [CategoryController::class, 'destroy']);

    // Menu Items / Dishes
    Route::get('/owner/menu-items', [MenuItemController::class, 'index']);
    Route::post('/owner/menu-items', [MenuItemController::class, 'store']);
    Route::put('/owner/menu-items/{id}', [MenuItemController::class, 'update']);
    Route::patch('/owner/menu-items/{id}/toggle-availability', [MenuItemController::class, 'toggleAvailability']);
    Route::delete('/owner/menu-items/{id}', [MenuItemController::class, 'destroy']);

    // Orders Management
    Route::get('/owner/orders', [OwnerOrderController::class, 'index']);
    Route::patch('/owner/orders/{id}/status', [OwnerOrderController::class, 'updateStatus']);

    // Dynamic Tables Management
    Route::get('/owner/tables', [TableController::class, 'index']);
    Route::post('/owner/tables', [TableController::class, 'store']);
    Route::post('/owner/tables/bulk-generate', [TableController::class, 'bulkGenerate']);
    Route::put('/owner/tables/{id}', [TableController::class, 'update']);
    Route::patch('/owner/tables/{id}/vacate', [TableController::class, 'vacate']);
    Route::delete('/owner/tables/{id}', [TableController::class, 'destroy']);
});
