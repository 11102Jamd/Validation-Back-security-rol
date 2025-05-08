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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('users', UserController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('inputs', InputController::class);
    Route::apiResource('products', ProductController::class);
});


Route::apiResource('purchase', PurchaseOrderController::class);
