@extends('layouts.adminlte')

@section('title', $journal->exists ? 'Edit Journal' : 'Create Journal')

@section('breadcrumb')
    @if(isset($breadcrumb_second))
        <li class="breadcrumb-item">{{ $breadcrumb_second }}</li>
    @endif
    <li class="breadcrumb-item active">{{ $journal->exists ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ $journal->exists ? 'Edit Journal' : 'Create Journal' }}</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ $journal->exists ? route('journals.update', $journal->id) : route('journals.store') }}" method="POST">
                    @csrf
                    @if($journal->exists)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="journal_number">Journal Number <span class="text-danger">*</span></label>
                                    <input type="text" name="journal_number" id="journal_number" class="form-control @error('journal_number') is-invalid @enderror"
                                           value="{{ old('journal_number', $journal->journal_number ?? $newJournalNumber) }}" required>
                                    @error('journal_number')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Type <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="general" {{ old('type', $journal->type) == 'general' ? 'selected' : '' }}>General</option>
                                        <option value="sales" {{ old('type', $journal->type) == 'sales' ? 'selected' : '' }}>Sales</option>
                                        <option value="purchase" {{ old('type', $journal->type) == 'purchase' ? 'selected' : '' }}>Purchase</option>
                                        <option value="cash" {{ old('type', $journal->type) == 'cash' ? 'selected' : '' }}>Cash</option>
                                    </select>
                                    @error('type')
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
                                    <label for="journal_date">Journal Date <span class="text-danger">*</span></label>
                                    <input type="date" name="journal_date" id="journal_date" class="form-control @error('journal_date') is-invalid @enderror"
                                           value="{{ old('journal_date', $journal->journal_date ?? date('Y-m-d')) }}" required>
                                    @error('journal_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                        <option value="draft" {{ old('status', $journal->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="posted" {{ old('status', $journal->status) == 'posted' ? 'selected' : '' }}>Posted</option>
                                        <option value="reversed" {{ old('status', $journal->status) == 'reversed' ? 'selected' : '' }}>Reversed</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $journal->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $journal->notes) }}</textarea>
                            @error('notes')
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
                        <a href="{{ route('journals.index') }}" class="btn btn-secondary float-right">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
