<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\RoleRequestController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\GuideController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\VendorSettingsController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\PublicCatalogController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --------------------------------------------------------
// Public Catalog & Browsing (No Auth Required)
// --------------------------------------------------------
Route::prefix('catalog')->group(function () {
    Route::get('/locations', [PublicCatalogController::class, 'getLocations']);
    Route::get('/locations/{id}', [PublicCatalogController::class, 'showLocation']);

    Route::get('/hotels', [PublicCatalogController::class, 'getHotels']);
    Route::get('/hotels/{id}', [PublicCatalogController::class, 'showHotel']);

    Route::get('/guides', [PublicCatalogController::class, 'getGuides']);
    Route::get('/guides/{id}', [PublicCatalogController::class, 'showGuide']);

    Route::get('/vehicles', [PublicCatalogController::class, 'getVehicles']);
    Route::get('/vehicles/{id}', [PublicCatalogController::class, 'showVehicle']);

    Route::get('/shops', [PublicCatalogController::class, 'getShops']);
    Route::get('/shops/{id}', [PublicCatalogController::class, 'showShop']);
});

// Authenticated User Info
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // --------------------------------------------------------
    // System Administration & Omnipotence Engine
    // --------------------------------------------------------
    Route::prefix('admin')->group(function () {
        // Role Requests Review
        Route::get('/role-requests', [RoleRequestController::class, 'index']);
        Route::patch('/role-requests/{id}', [RoleRequestController::class, 'reviewRequest']);

        // User Privilege Control
        Route::patch('/users/{id}/promote', [AdminController::class, 'promoteUser']);
        Route::delete('/users/{id}/privileges', [AdminController::class, 'revokePrivileges']);

        // Surrogate Listings (Created on behalf of vendors)
        Route::post('/surrogate/hotel', [AdminController::class, 'surrogateCreateHotel']);
        Route::post('/surrogate/guide', [AdminController::class, 'surrogateCreateGuide']);
        Route::post('/surrogate/vehicle', [AdminController::class, 'surrogateCreateVehicle']);
        Route::post('/surrogate/shop', [AdminController::class, 'surrogateCreateShop']);

        // Location Management
        Route::post('/locations', [LocationController::class, 'store']);
        Route::put('/locations/{id}', [LocationController::class, 'update']);
        Route::delete('/locations/{id}', [LocationController::class, 'destroy']);

        // Global Moderation & Reviews
        Route::delete('/entities/{type}/{id}', [AdminController::class, 'forceDeleteEntity']);
        Route::get('/reviews', [AdminController::class, 'indexReviews']);
        Route::delete('/reviews/{id}', [AdminController::class, 'deleteReview']);
    });
    
    // --------------------------------------------------------
    // Recommendation & Intelligence Engine
    // --------------------------------------------------------
    Route::post('/recommendations/bundle', [RecommendationController::class, 'generateBundle']);
    
    // --------------------------------------------------------
    // Role Requests (Tourist Application)
    // --------------------------------------------------------
    Route::post('/role-requests', [RoleRequestController::class, 'submitRequest']);

    // --------------------------------------------------------
    // MSME Vendor Portfolio Management (Self CRUD)
    // --------------------------------------------------------
    // Hotels
    Route::post('/vendor/hotel', [HotelController::class, 'store']);
    Route::put('/vendor/hotel/{id}', [HotelController::class, 'update']);
    Route::delete('/vendor/hotel/{id}', [HotelController::class, 'destroy']);

    // Guides
    Route::post('/vendor/guide', [GuideController::class, 'store']);
    Route::put('/vendor/guide/{id}', [GuideController::class, 'update']);
    Route::delete('/vendor/guide/{id}', [GuideController::class, 'destroy']);

    // Vehicles
    Route::post('/vendor/vehicle', [VehicleController::class, 'store']);
    Route::put('/vendor/vehicle/{id}', [VehicleController::class, 'update']);
    Route::delete('/vendor/vehicle/{id}', [VehicleController::class, 'destroy']);

    // Shops
    Route::post('/vendor/shop', [ShopController::class, 'store']);
    Route::put('/vendor/shop/{id}', [ShopController::class, 'update']);
    Route::delete('/vendor/shop/{id}', [ShopController::class, 'destroy']);

    // Shop Items (Vendor-Only)
    Route::post('/vendor/shop/{shopId}/items', [ShopController::class, 'storeItem']);
    Route::put('/vendor/shop/{shopId}/items/{itemId}', [ShopController::class, 'updateItem']);
    Route::delete('/vendor/shop/{shopId}/items/{itemId}', [ShopController::class, 'destroyItem']);

    // Vendor Resignation
    Route::post('/vendor/resign', [VendorSettingsController::class, 'resign']);
});