@extends('layouts.adminlte')

@section('title', $chartOfAccount->exists ? 'Edit Chart of Account' : 'Create Chart of Account')

@section('breadcrumb')
    @if(isset($breadcrumb_second))
        <li class="breadcrumb-item">{{ $breadcrumb_second }}</li>
    @endif
    <li class="breadcrumb-item active">{{ $chartOfAccount->exists ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ $chartOfAccount->exists ? 'Edit Chart of Account' : 'Create Chart of Account' }}</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ $chartOfAccount->exists ? route('chart-of-accounts.update', $chartOfAccount->id) : route('chart-of-accounts.store') }}" method="POST">
                    @csrf
                    @if($chartOfAccount->exists)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">Account Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                                           value="{{ old('code', $chartOfAccount->code) }}" required>
                                    @error('code')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Account Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $chartOfAccount->name) }}" required>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Account Type <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        <option value="asset" {{ old('type', $chartOfAccount->type) == 'asset' ? 'selected' : '' }}>Asset</option>
                                        <option value="liability" {{ old('type', $chartOfAccount->type) == 'liability' ? 'selected' : '' }}>Liability</option>
                                        <option value="equity" {{ old('type', $chartOfAccount->type) == 'equity' ? 'selected' : '' }}>Equity</option>
                                        <option value="income" {{ old('type', $chartOfAccount->type) == 'income' ? 'selected' : '' }}>Income</option>
                                        <option value="expense" {{ old('type', $chartOfAccount->type) == 'expense' ? 'selected' : '' }}>Expense</option>
                                    </select>
                                    @error('type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sub_type">Sub Type</label>
                                    <select name="sub_type" id="sub_type" class="form-control @error('sub_type') is-invalid @enderror">
                                        <option value="">Select Sub Type</option>
                                        <option value="current" {{ old('sub_type', $chartOfAccount->sub_type) == 'current' ? 'selected' : '' }}>Current</option>
                                        <option value="fixed" {{ old('sub_type', $chartOfAccount->sub_type) == 'fixed' ? 'selected' : '' }}>Fixed</option>
                                        <option value="other" {{ old('sub_type', $chartOfAccount->sub_type) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('sub_type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="balance">Balance</label>
                                    <input type="number" name="balance" id="balance" class="form-control @error('balance') is-invalid @enderror"
                                           value="{{ old('balance', $chartOfAccount->balance ?? 0) }}" step="0.01">
                                    @error('balance')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active">Status</label>
                                    <select name="is_active" id="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                        <option value="1" {{ old('is_active', $chartOfAccount->is_active ?? true) ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active', $chartOfAccount->is_active ?? true) ? '' : 'selected' }}>Inactive</option>
                                    </select>
                                    @error('is_active')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $chartOfAccount->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-secondary float-right">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
