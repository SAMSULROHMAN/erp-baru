@extends('layouts.adminlte')

@section('title', 'BOM Items')

@section('breadcrumb')
    <li class="breadcrumb-item active">BOM Items</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bill of Materials (BOM) List</h3>
            <div class="card-tools">
                <a href="{{ route('bom-items.create') }}" class="btn btn-primary btn-sm">
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
                            <th>Finished Product</th>
                            <th>Material</th>
                            <th>Quantity Required</th>
                            <th>Unit</th>
                            <th>Estimated Cost</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bomItems as $bom)
                            <tr>
                                <td>{{ $bom->id }}</td>
                                <td>{{ $bom->product->name ?? '-' }}</td>
                                <td>{{ $bom->materialProduct->name ?? '-' }}</td>
                                <td>{{ $bom->quantity_required }}</td>
                                <td>{{ $bom->unit }}</td>
                                <td>Rp {{ number_format($bom->estimated_cost, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('bom-items.edit', $bom->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('bom-items.destroy', $bom->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this BOM item?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No BOM items found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $bomItems->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
