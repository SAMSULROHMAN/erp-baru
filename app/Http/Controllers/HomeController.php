<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Category;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Invoice;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = [
            // Counts
            'productCount' => Product::count(),
            'supplierCount' => Supplier::count(),
            'customerCount' => Customer::count(),
            'userCount' => User::count(),
            'categoryCount' => Category::count(),
            'purchaseOrderCount' => PurchaseOrder::count(),
            'salesOrderCount' => SalesOrder::count(),
            'invoiceCount' => Invoice::count(),

            // Latest data
            'latestPurchaseOrders' => PurchaseOrder::with('supplier')->latest()->take(5)->get(),
            'latestSalesOrders' => SalesOrder::with('customer')->latest()->take(5)->get(),
            'lowStockProducts' => Product::whereRaw('stock_quantity <= reorder_level')->take(5)->get(),
        ];

        return view('dashboard.index', $data);
    }
}
