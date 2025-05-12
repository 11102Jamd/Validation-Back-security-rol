<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\OrderController\ProductController;
use App\Http\Controllers\PurchaseController\InputController;
use App\Http\Controllers\PurchaseController\PurchaseOrderController;
use App\Http\Controllers\PurchaseController\SupplierController;
use App\Http\Controllers\UserController\UserController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

// Rutas compartidas para todos los autenticados
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Cajeros solo pueden ver productos
    Route::middleware(['is_cashier'])->group(function () {
        Route::apiResource('products', ProductController::class)->only(['index', 'show']);
    });

    // Admins tienen acceso completo
    Route::middleware(['is_admin'])->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('inputs', InputController::class);
        Route::apiResource('purchase', PurchaseOrderController::class);
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    });

    // Todos los autenticados pueden ver productos (si lo deseas)
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
});
