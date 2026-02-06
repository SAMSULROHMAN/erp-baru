@extends('layouts.adminlte')

@section('title', 'Purchase Order Details - ' . $purchaseOrder->po_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">Purchase Orders</a></li>
    <li class="breadcrumb-item active">{{ $purchaseOrder->po_number }}</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Purchase Order Details</h3>
            <div class="card-tools">
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <a href="{{ route('purchase-orders.edit', $purchaseOrder->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>PO Number:</strong> {{ $purchaseOrder->po_number }}<br>
                    <strong>Supplier:</strong> {{ $purchaseOrder->supplier->name ?? '-' }}<br>
                    <strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('d/m/Y') }}<br>
                    <strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d/m/Y') : '-' }}<br>
                </div>
                <div class="col-md-6">
                    <strong>Status:</strong>
                    @switch($purchaseOrder->status)
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
                    @endswitch<br>
                    <strong>Subtotal:</strong> Rp {{ number_format($purchaseOrder->subtotal, 0, ',', '.') }}<br>
                    <strong>Tax:</strong> Rp {{ number_format($purchaseOrder->tax, 0, ',', '.') }}<br>
                    <strong>Total:</strong> Rp {{ number_format($purchaseOrder->total, 0, ',', '.') }}<br>
                </div>
            </div>
            @if($purchaseOrder->notes)
            <div class="row mt-3">
                <div class="col-md-12">
                    <strong>Notes:</strong><br>
                    {{ $purchaseOrder->notes }}
                </div>
            </div>
            @endif
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
