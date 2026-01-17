<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * API Routes
 *
 * These routes are loaded by the RouteServiceProvider and all of them will
 * be assigned to the "api" middleware group. Make something great!
 */

Route::middleware(["auth:sanctum"])->group(function () {
    Route::get("/user", function (Request $request) {
        return $request->user();
    });
});

/**
 * Future ADUX API endpoints will go here
 *
 * Example structure for Phase 2:
 *
 * Route::prefix('v1')->group(function () {
 *     // Public endpoints (read from Show DB)
 *     Route::get('/games', [GameController::class, 'index']);
 *     Route::get('/games/{game}', [GameController::class, 'show']);
 *
 *     // Authenticated endpoints (free tier)
 *     Route::middleware(['auth:sanctum'])->group(function () {
 *         Route::get('/games/{game}/reviews', [ReviewController::class, 'index']);
 *     });
 *
 *     // Rate-limited premium endpoints
 *     Route::middleware(['auth:sanctum', 'throttle:premium'])->group(function () {
 *         Route::get('/games/search', [GameController::class, 'search']);
 *     });
 * });
 */
