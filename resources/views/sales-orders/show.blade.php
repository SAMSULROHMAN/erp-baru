@extends('layouts.adminlte')

@section('title', 'Sales Order Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales-orders.index') }}">Sales Orders</a></li>
    <li class="breadcrumb-item active">{{ $salesOrder->so_number }}</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sales Order Details</h3>
            <div class="card-tools">
                <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <a href="{{ route('sales-orders.edit', $salesOrder->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>SO Number:</strong> {{ $salesOrder->so_number }}
                </div>
                <div class="col-md-6">
                    <strong>Order Date:</strong> {{ $salesOrder->order_date->format('d/m/Y') }}
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Customer:</strong> {{ $salesOrder->customer->name ?? '-' }}
                </div>
                <div class="col-md-6">
                    <strong>Status:</strong>
                    @switch($salesOrder->status)
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
                </div>
            </div>
            @if($salesOrder->notes)
            <div class="row mt-2">
                <div class="col-12">
                    <strong>Notes:</strong> {{ $salesOrder->notes }}
                </div>
            </div>
            @endif

            <hr>

            <h5>Order Items</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesOrder->items as $item)
                            <tr>
                                <td>{{ $item->product_name ?? '-' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No items found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                            <td>Rp {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Tax:</strong></td>
                            <td>Rp {{ number_format($salesOrder->tax, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Discount:</strong></td>
                            <td>Rp {{ number_format($salesOrder->discount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td><strong>Rp {{ number_format($salesOrder->total, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Amount Paid:</strong></td>
                            <td>Rp {{ number_format($salesOrder->amount_paid, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
