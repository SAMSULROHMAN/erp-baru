<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\SalesOrderController;
use App\Http\Controllers\Api\ProductionOrderController;
use App\Http\Controllers\Api\BomController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\ChartOfAccountController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;

// ===== USER MANAGEMENT MODULE =====
Route::prefix('users')->group(function () {
    Route::get('/roles', [UserController::class, 'getRoles']);
    Route::post('/create-role', [UserController::class, 'createRole']);
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{user}', [UserController::class, 'show']);
    Route::put('/{user}', [UserController::class, 'update']);
    Route::delete('/{user}', [UserController::class, 'destroy']);
});

    // ===== INVENTORY MANAGEMENT MODULE =====
    Route::prefix('products')->group(function () {
        Route::get('/low-stock', [ProductController::class, 'getLowStockProducts']);
        Route::get('/categories', [ProductController::class, 'getCategories']);
        Route::post('/create-category', [ProductController::class, 'createCategory']);
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{product}', [ProductController::class, 'show']);
        Route::put('/{product}', [ProductController::class, 'update']);
        Route::delete('/{product}', [ProductController::class, 'destroy']);
    });

    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::get('/{supplier}', [SupplierController::class, 'show']);
        Route::put('/{supplier}', [SupplierController::class, 'update']);
        Route::delete('/{supplier}', [SupplierController::class, 'destroy']);
    });

    // ===== PURCHASE & INVENTORY MODULE =====
    Route::prefix('purchase-orders')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index']);
        Route::post('/', [PurchaseOrderController::class, 'store']);
        Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
        Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update']);
        Route::patch('/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit']);
        Route::patch('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);
        Route::patch('/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);
        Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy']);
    });

    // ===== SALES MANAGEMENT MODULE =====
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/{customer}', [CustomerController::class, 'show']);
        Route::get('/{customer}/credit-info', [CustomerController::class, 'creditInfo']);
        Route::put('/{customer}', [CustomerController::class, 'update']);
        Route::delete('/{customer}', [CustomerController::class, 'destroy']);
    });

    Route::prefix('sales-orders')->group(function () {
        Route::get('/', [SalesOrderController::class, 'index']);
        Route::post('/', [SalesOrderController::class, 'store']);
        Route::get('/{salesOrder}', [SalesOrderController::class, 'show']);
        Route::put('/{salesOrder}', [SalesOrderController::class, 'update']);
        Route::patch('/{salesOrder}/confirm', [SalesOrderController::class, 'confirm']);
        Route::patch('/{salesOrder}/ship', [SalesOrderController::class, 'ship']);
        Route::patch('/{salesOrder}/cancel', [SalesOrderController::class, 'cancel']);
        Route::post('/{salesOrder}/create-invoice', [SalesOrderController::class, 'createInvoice']);
        Route::delete('/{salesOrder}', [SalesOrderController::class, 'destroy']);
    });

    // ===== PRODUCTION MANAGEMENT MODULE =====
    Route::prefix('production-orders')->group(function () {
        Route::get('/', [ProductionOrderController::class, 'index']);
        Route::post('/', [ProductionOrderController::class, 'store']);
        Route::get('/{productionOrder}', [ProductionOrderController::class, 'show']);
        Route::put('/{productionOrder}', [ProductionOrderController::class, 'update']);
        Route::patch('/{productionOrder}/schedule', [ProductionOrderController::class, 'schedule']);
        Route::patch('/{productionOrder}/start', [ProductionOrderController::class, 'startProduction']);
        Route::patch('/{productionOrder}/report-production', [ProductionOrderController::class, 'reportProduction']);
        Route::patch('/{productionOrder}/complete', [ProductionOrderController::class, 'complete']);
        Route::patch('/{productionOrder}/cancel', [ProductionOrderController::class, 'cancel']);
        Route::delete('/{productionOrder}', [ProductionOrderController::class, 'destroy']);
    });

    Route::prefix('bom')->group(function () {
        Route::get('/product/{productId}', [BomController::class, 'getByProduct']);
        Route::post('/items', [BomController::class, 'addItem']);
        Route::put('/items/{bomItem}', [BomController::class, 'updateItem']);
        Route::delete('/items/{bomItem}', [BomController::class, 'deleteItem']);
    });

    // ===== FINANCE & ACCOUNTING MODULE =====
    Route::prefix('chart-of-accounts')->group(function () {
        Route::get('/', [ChartOfAccountController::class, 'index']);
        Route::post('/', [ChartOfAccountController::class, 'store']);
        Route::get('/{chartOfAccount}', [ChartOfAccountController::class, 'show']);
        Route::get('/{chartOfAccount}/balance', [ChartOfAccountController::class, 'getBalance']);
        Route::put('/{chartOfAccount}', [ChartOfAccountController::class, 'update']);
        Route::delete('/{chartOfAccount}', [ChartOfAccountController::class, 'destroy']);
    });

    Route::prefix('journals')->group(function () {
        Route::get('/', [JournalController::class, 'index']);
        Route::post('/', [JournalController::class, 'store']);
        Route::get('/{journal}', [JournalController::class, 'show']);
        Route::put('/{journal}', [JournalController::class, 'update']);
        Route::patch('/{journal}/post', [JournalController::class, 'post']);
        Route::delete('/{journal}', [JournalController::class, 'destroy']);
    });

    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('/', [InvoiceController::class, 'store']);
        Route::get('/{invoice}', [InvoiceController::class, 'show']);
        Route::put('/{invoice}', [InvoiceController::class, 'update']);
        Route::patch('/{invoice}/send', [InvoiceController::class, 'send']);
        Route::patch('/{invoice}/record-payment', [InvoiceController::class, 'recordPayment']);
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy']);
    });

    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::get('/{payment}', [PaymentController::class, 'show']);
        Route::put('/{payment}', [PaymentController::class, 'update']);
        Route::patch('/{payment}/confirm', [PaymentController::class, 'confirm']);
        Route::patch('/{payment}/cancel', [PaymentController::class, 'cancel']);
        Route::delete('/{payment}', [PaymentController::class, 'destroy']);
    });
