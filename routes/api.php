<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;

Route::apiResource('products', ProductController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('customers', CustomerController::class);

// Ventas (no se editan ni se borran: se cambia su estado)
Route::apiResource('sales', SaleController::class)->only(['index', 'show', 'store']);
Route::patch('sales/{sale}/status', [SaleController::class, 'updateStatus']);

// Inventario
Route::get('inventory/movements', [InventoryController::class, 'index']);
Route::post('inventory/movements', [InventoryController::class, 'store']);

// Panel principal y reportes
Route::get('dashboard', DashboardController::class);
Route::get('reports/summary', [ReportController::class, 'summary']);
