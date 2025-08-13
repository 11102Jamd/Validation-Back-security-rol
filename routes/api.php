<?php

use Illuminate\http\Request;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ManufacturingController\ManufacturingController;
use App\Http\Controllers\OrderController\OrderController;
use App\Http\Controllers\OrderController\ProductController;
use App\Http\Controllers\PurchaseController\InputController;
use App\Http\Controllers\PurchaseController\PurchaseOrderController;
use App\Http\Controllers\PurchaseController\SupplierController;
use App\Http\Controllers\Reports\ManufacturingPdfController;
use App\Http\Controllers\Reports\PurchaseOrderPdfController;
use App\Http\Controllers\UserController\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Rutas compartidas para todos los autenticados
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    /**
     * Rutas especificas para el usuario Administrador
     */
    Route::middleware(['is_admin'])->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('inputs', InputController::class);
        Route::apiResource('purchase', PurchaseOrderController::class)->except(['destroy']);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('order', OrderController::class)->except(['destroy']);
        Route::apiResource('manufacturing', ManufacturingController::class);
    });

    /**
     * Rutas especificas para el usuario Cajero
     */
    Route::middleware(['is_cashier'])->group(function () {
        Route::apiResource('products', ProductController::class)->only(['index', 'show']);
        Route::apiResource('order', OrderController::class)->only(['index', 'store']);
        Route::apiResource('purchase', PurchaseOrderController::class)->only(['index', 'store']);
    });

    /**
     * Rutas para el usuario panadero
     */
    Route::middleware(['is_baker'])->group(function () {
        Route::apiResource('manufacturing', ManufacturingController::class);
        Route::apiResource('inputs', InputController::class)->only(['index', 'show', 'store', 'update']);
    });

    // Rutas comunes para todos los autenticados
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
});

Route::post('/purchase/exportPdf', [PurchaseOrderPdfController::class, 'exportPdf']);
Route::post('/manufacturing/exportPdf', [ManufacturingPdfController::class, 'exportPdf']);
