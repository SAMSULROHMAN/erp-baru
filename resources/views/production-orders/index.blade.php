@extends('layouts.adminlte')

@section('title', 'Production Orders')

@section('breadcrumb')
    <li class="breadcrumb-item active">Production Orders</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Production Orders List</h3>
            <div class="card-tools">
                <a href="{{ route('production-orders.create') }}" class="btn btn-primary btn-sm">
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
                            <th>Production Number</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Start Date</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productionOrders as $po)
                            <tr>
                                <td>{{ $po->id }}</td>
                                <td>{{ $po->pro_number }}</td>
                                <td>{{ $po->product->name ?? '-' }}</td>
                                <td>{{ $po->quantity }} {{ $po->product->unit ?? '' }}</td>
                                <td>{{ $po->start_date->format('d/m/Y') }}</td>
                                <td>
                                    @switch($po->status)
                                        @case('draft')
                                            <span class="badge badge-secondary">Draft</span>
                                            @break
                                        @case('scheduled')
                                            <span class="badge badge-info">Scheduled</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge badge-primary">In Progress</span>
                                            @break
                                        @case('completed')
                                            <span class="badge badge-success">Completed</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge badge-danger">Cancelled</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <a href="{{ route('production-orders.show', $po->id) }}" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('production-orders.edit', $po->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('production-orders.destroy', $po->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this production order?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No production orders found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $productionOrders->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
