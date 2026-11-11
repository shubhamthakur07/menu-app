<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerFeedbackController;
use App\Http\Controllers\LoyaltyProgramController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Owner\CategoryController;
use App\Http\Controllers\Owner\CustomerFeedbackController as OwnerFeedbackController;
use App\Http\Controllers\Owner\LoyaltyController as OwnerLoyaltyController;
use App\Http\Controllers\Owner\MenuItemController;
use App\Http\Controllers\Owner\MenuScanController;
use App\Http\Controllers\Owner\OwnerOrderController;
use App\Http\Controllers\Owner\RestaurantController;
use App\Http\Controllers\Owner\TableController;
use App\Http\Controllers\Owner\TableServiceCallController as OwnerTableServiceCallController;
use App\Http\Controllers\PublicMenuController;
use App\Http\Controllers\TableServiceCallController;
use App\Http\Controllers\UploadController;
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

// Customer Loyalty Program Routes
Route::get('/r/{slug}/loyalty-status', [LoyaltyProgramController::class, 'status']);
Route::post('/r/{slug}/claim-loyalty-reward', [LoyaltyProgramController::class, 'claim']);

// Customer Table Service Calls & Feedback Routes
Route::post('/r/{slug}/service-call', [TableServiceCallController::class, 'store']);
Route::post('/r/{slug}/feedback', [CustomerFeedbackController::class, 'store']);

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

    // AI Photo-to-Menu Scanner
    Route::post('/owner/menu/scan-image', [MenuScanController::class, 'scan']);
    Route::post('/owner/menu/import-scanned', [MenuScanController::class, 'import']);

    // Orders Management
    Route::get('/owner/orders', [OwnerOrderController::class, 'index']);
    Route::patch('/owner/orders/{id}/status', [OwnerOrderController::class, 'updateStatus']);

    // Loyalty Program Management
    Route::get('/owner/loyalty', [OwnerLoyaltyController::class, 'index']);
    Route::put('/owner/loyalty', [OwnerLoyaltyController::class, 'update']);

    // Table Service Calls Management
    Route::get('/owner/service-calls', [OwnerTableServiceCallController::class, 'index']);
    Route::patch('/owner/service-calls/{id}/status', [OwnerTableServiceCallController::class, 'updateStatus']);
    Route::delete('/owner/service-calls/clear-completed', [OwnerTableServiceCallController::class, 'clearCompleted']);

    // Customer Reviews & Feedback
    Route::get('/owner/feedbacks', [OwnerFeedbackController::class, 'index']);

    // Dynamic Tables Management
    Route::get('/owner/tables', [TableController::class, 'index']);
    Route::post('/owner/tables', [TableController::class, 'store']);
    Route::post('/owner/tables/bulk-generate', [TableController::class, 'bulkGenerate']);
    Route::put('/owner/tables/{id}', [TableController::class, 'update']);
    Route::patch('/owner/tables/{id}/vacate', [TableController::class, 'vacate']);
    Route::delete('/owner/tables/{id}', [TableController::class, 'destroy']);
});
