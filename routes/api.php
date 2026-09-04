<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WatchCatalogController;

// ─────────────────────────────────────────────
//  PUBLIC CATALOG
// ─────────────────────────────────────────────
Route::get('/watches', [WatchCatalogController::class, 'index']);
Route::get('/watches/{id}', [WatchCatalogController::class, 'show']);
Route::post('/sell-offer', [WatchCatalogController::class, 'storeSellOffer']);
Route::post('/sourcing-request', [WatchCatalogController::class, 'storeSourcingRequest']);

// ─────────────────────────────────────────────
//  ADMIN (Seller Center)
// ─────────────────────────────────────────────
Route::post('/admin/login', [WatchCatalogController::class, 'adminLogin']);

Route::prefix('admin')->group(function () {
    Route::post('/watches', [WatchCatalogController::class, 'storeProduct']);
    Route::put('/watches/{id}', [WatchCatalogController::class, 'updateProduct']);
    Route::delete('/watches/{id}', [WatchCatalogController::class, 'destroyProduct']);
    Route::get('/sell-offers', [WatchCatalogController::class, 'getSellOffers']);
    Route::get('/sourcing-requests', [WatchCatalogController::class, 'getSourcingRequests']);
});
