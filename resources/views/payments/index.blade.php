@extends('layouts.adminlte')

@section('title', 'Payments')

@section('breadcrumb')
    <li class="breadcrumb-item active">Payments</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payments List</h3>
            <div class="card-tools">
                <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
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
                            <th>Payment Number</th>
                            <th>Type</th>
                            <th>Customer/Supplier</th>
                            <th>Payment Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->id }}</td>
                                <td>{{ $payment->payment_number }}</td>
                                <td>
                                    @if($payment->payment_type === 'customer_payment')
                                        <span class="badge badge-success">Customer Payment</span>
                                    @else
                                        <span class="badge badge-warning">Supplier Payment</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $payment->customer->name ?? $payment->supplier->name ?? '-' }}
                                </td>
                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td>
                                    @switch($payment->payment_method)
                                        @case('cash')
                                            <span class="badge badge-secondary">Cash</span>
                                            @break
                                        @case('bank_transfer')
                                            <span class="badge badge-info">Bank Transfer</span>
                                            @break
                                        @case('check')
                                            <span class="badge badge-warning">Check</span>
                                            @break
                                        @case('credit_card')
                                            <span class="badge badge-primary">Credit Card</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    @switch($payment->status)
                                        @case('pending')
                                            <span class="badge badge-warning">Pending</span>
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
                                    <a href="{{ route('payments.show', $payment->id) }}" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('payments.edit', $payment->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this payment?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No payments found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $payments->links() }}
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
