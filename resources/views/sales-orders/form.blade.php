@extends('layouts.adminlte')

@section('title', $salesOrder->exists ? 'Edit Sales Order' : 'Create Sales Order')

@section('breadcrumb')
    @if(isset($breadcrumb_second))
        <li class="breadcrumb-item">{{ $breadcrumb_second }}</li>
    @endif
    <li class="breadcrumb-item active">{{ $salesOrder->exists ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ $salesOrder->exists ? 'Edit Sales Order' : 'Create Sales Order' }}</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ $salesOrder->exists ? route('sales-orders.update', $salesOrder->id) : route('sales-orders.store') }}" method="POST">
                    @csrf
                    @if($salesOrder->exists)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_id">Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" id="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id', $salesOrder->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="so_number">SO Number <span class="text-danger">*</span></label>
                                    <input type="text" name="so_number" id="so_number" class="form-control @error('so_number') is-invalid @enderror"
                                           value="{{ old('so_number', $salesOrder->so_number ?? $newSONumber) }}" required>
                                    @error('so_number')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="order_date">Order Date <span class="text-danger">*</span></label>
                                    <input type="date" name="order_date" id="order_date" class="form-control @error('order_date') is-invalid @enderror"
                                           value="{{ old('order_date', $salesOrder->order_date ?? date('Y-m-d')) }}" required>
                                    @error('order_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="required_date">Required Date</label>
                                    <input type="date" name="required_date" id="required_date" class="form-control @error('required_date') is-invalid @enderror"
                                           value="{{ old('required_date', $salesOrder->required_date) }}">
                                    @error('required_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                        <option value="draft" {{ old('status', $salesOrder->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="confirmed" {{ old('status', $salesOrder->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="shipped" {{ old('status', $salesOrder->status) == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                        <option value="delivered" {{ old('status', $salesOrder->status) == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="cancelled" {{ old('status', $salesOrder->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $salesOrder->notes) }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="subtotal">Subtotal</label>
                                    <input type="number" name="subtotal" id="subtotal" class="form-control @error('subtotal') is-invalid @enderror"
                                           value="{{ old('subtotal', $salesOrder->subtotal ?? 0) }}" step="0.01">
                                    @error('subtotal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tax">Tax</label>
                                    <input type="number" name="tax" id="tax" class="form-control @error('tax') is-invalid @enderror"
                                           value="{{ old('tax', $salesOrder->tax ?? 0) }}" step="0.01">
                                    @error('tax')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="discount">Discount</label>
                                    <input type="number" name="discount" id="discount" class="form-control @error('discount') is-invalid @enderror"
                                           value="{{ old('discount', $salesOrder->discount ?? 0) }}" step="0.01">
                                    @error('discount')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="total">Total</label>
                                    <input type="number" name="total" id="total" class="form-control @error('total') is-invalid @enderror"
                                           value="{{ old('total', $salesOrder->total ?? 0) }}" step="0.01">
                                    @error('total')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary float-right">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
