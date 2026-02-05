@extends('layouts.adminlte')

@section('title', 'Sales Orders')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales Orders</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sales Orders List</h3>
            <div class="card-tools">
                <a href="{{ route('sales-orders.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New
                </a>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>SO Number</th>
                            <th>Customer</th>
                            <th>Order Date</th>
                            <th>Total</th>
                            <th>Amount Paid</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesOrders as $so)
                            <tr>
                                <td>{{ $so->id }}</td>
                                <td>{{ $so->so_number }}</td>
                                <td>{{ $so->customer->name ?? '-' }}</td>
                                <td>{{ $so->order_date->format('d/m/Y') }}</td>
                                <td>Rp {{ number_format($so->total, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($so->amount_paid, 0, ',', '.') }}</td>
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
                                <td>
                                    <a href="{{ route('sales-orders.show', $so->id) }}" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('sales-orders.edit', $so->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('sales-orders.destroy', $so->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this sales order?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No sales orders found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $salesOrders->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
