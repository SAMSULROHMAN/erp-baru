<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Resource Routes
Route::resources([
    'users' => App\Http\Controllers\UserController::class,
    'roles' => App\Http\Controllers\RoleController::class,
    'suppliers' => App\Http\Controllers\SupplierController::class,
    'customers' => App\Http\Controllers\CustomerController::class,
    'products' => App\Http\Controllers\ProductController::class,
    'categories' => App\Http\Controllers\CategoryController::class,
    'purchase-orders' => App\Http\Controllers\PurchaseOrderController::class,
    'sales-orders' => App\Http\Controllers\SalesOrderController::class,
    'invoices' => App\Http\Controllers\InvoiceController::class,
    'production-orders' => App\Http\Controllers\ProductionOrderController::class,
    'bom-items' => App\Http\Controllers\BomItemController::class,
    'chart-of-accounts' => App\Http\Controllers\ChartOfAccountController::class,
    'journals' => App\Http\Controllers\JournalController::class,
    'payments' => App\Http\Controllers\PaymentController::class,
    'stock-movements' => App\Http\Controllers\StockMovementController::class,
]);
