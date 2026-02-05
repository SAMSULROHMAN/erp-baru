@extends('layouts.adminlte')

@section('title', $title)

@section('breadcrumb')
    @if(isset($breadcrumb_second))
        <li class="breadcrumb-item">{{ $breadcrumb_second }}</li>
    @endif
    @if(isset($breadcrumb_third))
        <li class="breadcrumb-item">{{ $breadcrumb_third }}</li>
    @endif
    <li class="breadcrumb-item active">{{ $title }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }}</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($method) && $method === 'PUT')
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        @foreach($fields as $field)
                            <div class="form-group">
                                <label for="{{ $field['name'] }}">{{ $field['label'] }}
                                    @if(isset($field['required']) && $field['required'])
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                @if($field['type'] === 'text')
                                    <input type="text"
                                           name="{{ $field['name'] }}"
                                           id="{{ $field['name'] }}"
                                           class="form-control @error($field['name']) is-invalid @enderror"
                                           value="{{ old($field['name'], $field['value'] ?? '') }}"
                                           @if(isset($field['required']) && $field['required']) required @endif
                                           @if(isset($field['readonly']) && $field['readonly']) readonly @endif>

                                @elseif($field['type'] === 'email')
                                    <input type="email"
                                           name="{{ $field['name'] }}"
                                           id="{{ $field['name'] }}"
                                           class="form-control @error($field['name']) is-invalid @enderror"
                                           value="{{ old($field['name'], $field['value'] ?? '') }}"
                                           @if(isset($field['required']) && $field['required']) required @endif>

                                @elseif($field['type'] === 'password')
                                    <input type="password"
                                           name="{{ $field['name'] }}"
                                           id="{{ $field['name'] }}"
                                           class="form-control @error($field['name']) is-invalid @enderror"
                                           @if(isset($field['required']) && $field['required']) required @endif>

                                @elseif($field['type'] === 'number')
                                    <input type="number"
                                           name="{{ $field['name'] }}"
                                           id="{{ $field['name'] }}"
                                           class="form-control @error($field['name']) is-invalid @enderror"
                                           value="{{ old($field['name'], $field['value'] ?? 0) }}"
                                           step="any"
                                           @if(isset($field['required']) && $field['required']) required @endif>

                                @elseif($field['type'] === 'textarea')
                                    <textarea name="{{ $field['name'] }}"
                                              id="{{ $field['name'] }}"
                                              class="form-control @error($field['name']) is-invalid @enderror"
                                              rows="{{ $field['rows'] ?? 3 }}"
                                              @if(isset($field['required']) && $field['required']) required @endif>{{ old($field['name'], $field['value'] ?? '') }}</textarea>

                                @elseif($field['type'] === 'select')
                                    <select name="{{ $field['name'] }}"
                                            id="{{ $field['name'] }}"
                                            class="form-control @error($field['name']) is-invalid @enderror"
                                            @if(isset($field['required']) && $field['required']) required @endif>
                                        <option value="">Select {{ $field['label'] }}</option>
                                        @foreach($field['options'] as $key => $option)
                                            <option value="{{ $key }}" {{ old($field['name'], $field['value'] ?? '') == $key ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>

                                @elseif($field['type'] === 'select_relationship')
                                    <select name="{{ $field['name'] }}"
                                            id="{{ $field['name'] }}"
                                            class="form-control @error($field['name']) is-invalid @enderror"
                                            @if(isset($field['required']) && $field['required']) required @endif>
                                        <option value="">Select {{ $field['label'] }}</option>
                                        @foreach($field['options'] as $option)
                                            <option value="{{ $option->id }}" {{ old($field['name'], $field['value'] ?? '') == $option->id ? 'selected' : '' }}>
                                                {{ $option->{$field['option_label']} }}
                                            </option>
                                        @endforeach
                                    </select>

                                @elseif($field['type'] === 'date')
                                    <input type="date"
                                           name="{{ $field['name'] }}"
                                           id="{{ $field['name'] }}"
                                           class="form-control @error($field['name']) is-invalid @enderror"
                                           value="{{ old($field['name'], $field['value'] ?? '') }}"
                                           @if(isset($field['required']) && $field['required']) required @endif>

                                @elseif($field['type'] === 'checkbox')
                                    <div class="form-check">
                                        <input type="checkbox"
                                               name="{{ $field['name'] }}"
                                               id="{{ $field['name'] }}"
                                               class="form-check-input"
                                               value="1"
                                               {{ old($field['name'], $field['value'] ?? '') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ $field['name'] }}">
                                            {{ $field['checkbox_label'] ?? '' }}
                                        </label>
                                    </div>

                                @elseif($field['type'] === 'select_enum')
                                    <select name="{{ $field['name'] }}"
                                            id="{{ $field['name'] }}"
                                            class="form-control @error($field['name']) is-invalid @enderror"
                                            @if(isset($field['required']) && $field['required']) required @endif>
                                        @foreach($field['options'] as $key => $option)
                                            <option value="{{ $key }}" {{ old($field['name'], $field['value'] ?? '') == $key ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>

                                @endif

                                @error($field['name'])
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <a href="{{ route($cancel_route) }}" class="btn btn-secondary float-right">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Add any custom JavaScript here
        });
    </script>
@endpush
