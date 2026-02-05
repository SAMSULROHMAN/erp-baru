@extends('layouts.adminlte')

@section('title', $title)

@section('breadcrumb')
    @if(isset($breadcrumb_second))
        <li class="breadcrumb-item">{{ $breadcrumb_second }}</li>
    @endif
    <li class="breadcrumb-item active">{{ $title }}</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $title }} List</h3>
            @if(isset($create_route))
                <div class="card-tools">
                    <a href="{{ route($create_route) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>
            @endif
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            @if(isset($searchable) && $searchable)
                <div class="mb-3">
                    <form action="{{ route($index_route) }}" method="GET" class="form-inline">
                        <div class="input-group" style="width: 300px;">
                            <input type="text" name="search" class="form-control float-right"
                                   placeholder="Search..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            @foreach($columns as $column)
                                <th>{{ $column['label'] }}</th>
                            @endforeach
                            @if(isset($actions) && $actions)
                                <th width="150">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                @foreach($columns as $column)
                                    <td>
                                        @if(isset($column['type']) && $column['type'] === 'status')
                                            @if($item->{$column['field']} === 'active' || $item->{$column['field']} === true)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        @elseif(isset($column['type']) && $column['type'] === 'currency')
                                            Rp {{ number_format($item->{$column['field']}, 0, ',', '.') }}
                                        @elseif(isset($column['type']) && $column['type'] === 'date')
                                            {{ \Carbon\Carbon::parse($item->{$column['field']})->format('d/m/Y') }}
                                        @elseif(isset($column['type']) && $column['type'] === 'relation')
                                            {{ $item->{$column['relation']}->{$column['relation_field']} ?? '-' }}
                                        @else
                                            {{ $item->{$column['field']} ?? '-' }}
                                        @endif
                                    </td>
                                @endforeach
                                @if(isset($actions) && $actions)
                                    <td>
                                        @if(isset($show_route))
                                            <a href="{{ route($show_route, $item->id) }}" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                        @if(isset($edit_route))
                                            <a href="{{ route($edit_route, $item->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(isset($delete_route))
                                            <form action="{{ route($delete_route, $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this item?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + (isset($actions) && $actions ? 1 : 0) }}" class="text-center">
                                    No data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($data, 'links'))
                <div class="mt-3">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
@endsection
