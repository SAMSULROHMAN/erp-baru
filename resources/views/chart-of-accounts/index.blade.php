@extends('layouts.adminlte')

@section('title', 'Chart of Accounts')

@section('breadcrumb')
    <li class="breadcrumb-item active">Chart of Accounts</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Chart of Accounts List</h3>
            <div class="card-tools">
                <a href="{{ route('chart-of-accounts.create') }}" class="btn btn-primary btn-sm">
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
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Sub Type</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($chartOfAccounts as $account)
                            <tr>
                                <td>{{ $account->id }}</td>
                                <td>{{ $account->code }}</td>
                                <td>{{ $account->name }}</td>
                                <td>
                                    @switch($account->type)
                                        @case('asset')
                                            <span class="badge badge-success">Asset</span>
                                            @break
                                        @case('liability')
                                            <span class="badge badge-danger">Liability</span>
                                            @break
                                        @case('equity')
                                            <span class="badge badge-info">Equity</span>
                                            @break
                                        @case('income')
                                            <span class="badge badge-success">Income</span>
                                            @break
                                        @case('expense')
                                            <span class="badge badge-warning">Expense</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $account->sub_type ?? '-' }}</td>
                                <td>Rp {{ number_format($account->balance, 0, ',', '.') }}</td>
                                <td>
                                    @if($account->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('chart-of-accounts.show', $account->id) }}" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('chart-of-accounts.edit', $account->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('chart-of-accounts.destroy', $account->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this account?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No chart of accounts found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $chartOfAccounts->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
