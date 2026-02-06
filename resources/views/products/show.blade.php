@extends('layouts.adminlte')

@section('title', 'Product Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">{{ $product->code }}</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Product Details</h3>
            <div class="card-tools">
                <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Product Code:</strong> {{ $product->code }}
                </div>
                <div class="col-md-6">
                    <strong>Product Name:</strong> {{ $product->name }}
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Category:</strong> {{ $product->category->name ?? '-' }}
                </div>
                <div class="col-md-6">
                    <strong>Status:</strong>
                    @if($product->status === 'active')
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-danger">Inactive</span>
                    @endif
                </div>
            </div>
            @if($product->description)
            <div class="row mt-2">
                <div class="col-12">
                    <strong>Description:</strong> {{ $product->description }}
                </div>
            </div>
            @endif

            <hr>

            <h5>Pricing Information</h5>
            <div class="row">
                <div class="col-md-4">
                    <strong>Cost Price:</strong><br>
                    Rp {{ number_format($product->cost_price, 0, ',', '.') }}
                </div>
                <div class="col-md-4">
                    <strong>Selling Price:</strong><br>
                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                </div>
                <div class="col-md-4">
                    <strong>Unit:</strong><br>
                    {{ $product->unit ?? 'N/A' }}
                </div>
            </div>

            <hr>

            <h5>Stock Information</h5>
            <div class="row">
                <div class="col-md-4">
                    <strong>Current Stock:</strong><br>
                    @if($product->stock_quantity <= $product->reorder_level)
                        <span class="badge badge-warning">{{ $product->stock_quantity }}</span>
                    @else
                        {{ $product->stock_quantity }}
                    @endif
                    {{ $product->unit }}
                </div>
                <div class="col-md-4">
                    <strong>Reorder Level:</strong><br>
                    {{ $product->reorder_level }}
                </div>
                <div class="col-md-4">
                    <strong>Stock Status:</strong><br>
                    @if($product->stock_quantity <= $product->reorder_level)
                        <span class="badge badge-danger">Low Stock</span>
                    @else
                        <span class="badge badge-success">In Stock</span>
                    @endif
                </div>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
