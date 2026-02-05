@extends('layouts.adminlte')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <!-- Info boxes -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-box"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Products</span>
                    <span class="info-box-number">{{ $productCount }}</span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Suppliers</span>
                    <span class="info-box-number">{{ $supplierCount }}</span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->

        <!-- fix for small devices only -->
        <div class="clearfix hidden-md-up"></div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-hand-holding-usd"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Customers</span>
                    <span class="info-box-number">{{ $customerCount }}</span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Users</span>
                    <span class="info-box-number">{{ $userCount }}</span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

    <div class="row">
        <!-- Left col -->
        <div class="col-md-8">
            <!-- TABLE: LATEST ORDERS -->
            <div class="card">
                <div class="card-header border-transparent">
                    <h3 class="card-title">Latest Purchase Orders</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th>PO Number</th>
                                    <th>Supplier</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestPurchaseOrders as $po)
                                    <tr>
                                        <td><a href="{{ route('purchase-orders.show', $po->id) }}">{{ $po->po_number }}</a></td>
                                        <td>{{ $po->supplier->name ?? '-' }}</td>
                                        <td>Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                                        <td>
                                            @switch($po->status)
                                                @case('draft')
                                                    <span class="badge badge-secondary">Draft</span>
                                                    @break
                                                @case('submitted')
                                                    <span class="badge badge-primary">Submitted</span>
                                                    @break
                                                @case('received')
                                                    <span class="badge badge-success">Received</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge badge-danger">Cancelled</span>
                                                    @break
                                            @endswitch
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No purchase orders found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- /.table-responsive -->
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-secondary float-right">View All Purchase Orders</a>
                </div>
                <!-- /.card-footer -->
            </div>
            <!-- /.card -->

            <!-- TABLE: LATEST SALES ORDERS -->
            <div class="card">
                <div class="card-header border-transparent">
                    <h3 class="card-title">Latest Sales Orders</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th>SO Number</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestSalesOrders as $so)
                                    <tr>
                                        <td><a href="{{ route('sales-orders.show', $so->id) }}">{{ $so->so_number }}</a></td>
                                        <td>{{ $so->customer->name ?? '-' }}</td>
                                        <td>Rp {{ number_format($so->total, 0, ',', '.') }}</td>
                                        <td>
                                            @switch($so->status)
                                                @case('draft')
                                                    <span class="badge badge-secondary">Draft</span>
                                                    @break
                                                @case('confirmed')
                                                    <span class="badge badge-primary">Confirmed</span>
                                                    @break
                                                @case('shipped')
                                                    <span class="badge badge-info">Shipped</span>
                                                    @break
                                                @case('delivered')
                                                    <span class="badge badge-success">Delivered</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge badge-danger">Cancelled</span>
                                                    @break
                                            @endswitch
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No sales orders found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- /.table-responsive -->
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    <a href="{{ route('sales-orders.index') }}" class="btn btn-sm btn-secondary float-right">View All Sales Orders</a>
                </div>
                <!-- /.card-footer -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->

        <div class="col-md-4">
            <!-- Info Boxes Style 2 -->
            <div class="info-box mb-3 bg-warning">
                <span class="info-box-icon"><i class="fas fa-tag"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Categories</span>
                    <span class="info-box-number">{{ $categoryCount }}</span>
                </div>
            </div>
            <div class="info-box mb-3 bg-success">
                <span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Purchase Orders</span>
                    <span class="info-box-number">{{ $purchaseOrderCount }}</span>
                </div>
            </div>
            <div class="info-box mb-3 bg-info">
                <span class="info-box-icon"><i class="far fa-comment"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Sales Orders</span>
                    <span class="info-box-number">{{ $salesOrderCount }}</span>
                </div>
            </div>
            <div class="info-box mb-3 bg-danger">
                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Invoices</span>
                    <span class="info-box-number">{{ $invoiceCount }}</span>
                </div>
            </div>

            <!-- PRODUCT LIST -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Low Stock Products</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                        @forelse($lowStockProducts as $product)
                            <li class="item">
                                <div class="product-info">
                                    <a href="{{ route('products.show', $product->id) }}" class="product-title">
                                        {{ $product->name }}
                                        <span class="badge badge-warning float-right">{{ $product->stock_quantity }} left</span>
                                    </a>
                                    <span class="product-description">
                                        Code: {{ $product->code }}
                                    </span>
                                </div>
                            </li>
                        @empty
                            <li class="item text-center">No low stock products</li>
                        @endforelse
                    </ul>
                </div>
                <!-- /.card-body -->
                <div class="card-footer text-center">
                    <a href="{{ route('products.index') }}" class="uppercase">View All Products</a>
                </div>
                <!-- /.card-footer -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
@endsection
