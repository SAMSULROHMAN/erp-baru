@extends('layouts.adminlte')

@section('title', 'Journals')

@section('breadcrumb')
    <li class="breadcrumb-item active">Journals</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Journals List</h3>
            <div class="card-tools">
                <a href="{{ route('journals.create') }}" class="btn btn-primary btn-sm">
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
                            <th>Journal Number</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journals as $journal)
                            <tr>
                                <td>{{ $journal->id }}</td>
                                <td>{{ $journal->journal_number }}</td>
                                <td>
                                    @switch($journal->type)
                                        @case('general')
                                            <span class="badge badge-secondary">General</span>
                                            @break
                                        @case('sales')
                                            <span class="badge badge-info">Sales</span>
                                            @break
                                        @case('purchase')
                                            <span class="badge badge-warning">Purchase</span>
                                            @break
                                        @case('cash')
                                            <span class="badge badge-success">Cash</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $journal->journal_date->format('d/m/Y') }}</td>
                                <td>{{ Str::limit($journal->description, 50) }}</td>
                                <td>
                                    @switch($journal->status)
                                        @case('draft')
                                            <span class="badge badge-secondary">Draft</span>
                                            @break
                                        @case('posted')
                                            <span class="badge badge-success">Posted</span>
                                            @break
                                        @case('reversed')
                                            <span class="badge badge-danger">Reversed</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <a href="{{ route('journals.show', $journal->id) }}" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('journals.edit', $journal->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('journals.destroy', $journal->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this journal?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No journals found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $journals->links() }}
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
