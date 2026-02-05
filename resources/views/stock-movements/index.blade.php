@extends('layouts.adminlte')

@section('title', 'Stock Movements')

@section('breadcrumb')
    <li class="breadcrumb-item active">Stock Movements</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Stock Movements List</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Reference</th>
                            <th>Notes</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockMovements as $movement)
                            <tr>
                                <td>{{ $movement->id }}</td>
                                <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $movement->product->name ?? '-' }}</td>
                                <td>
                                    @if($movement->type === 'in')
                                        <span class="badge badge-success">In</span>
                                    @else
                                        <span class="badge badge-danger">Out</span>
                                    @endif
                                </td>
                                <td>{{ $movement->quantity }}</td>
                                <td>
                                    @if($movement->reference_number)
                                        <span class="badge badge-info">{{ $movement->reference_type }}</span>
                                        {{ $movement->reference_number }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ Str::limit($movement->notes, 30) }}</td>
                                <td>{{ $movement->createdBy->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No stock movements found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $stockMovements->links() }}
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
