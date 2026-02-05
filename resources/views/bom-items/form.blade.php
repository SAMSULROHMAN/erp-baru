@extends('layouts.adminlte')

@section('title', $bomItem->exists ? 'Edit BOM Item' : 'Create BOM Item')

@section('breadcrumb')
    @if(isset($breadcrumb_second))
        <li class="breadcrumb-item">{{ $breadcrumb_second }}</li>
    @endif
    <li class="breadcrumb-item active">{{ $bomItem->exists ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ $bomItem->exists ? 'Edit BOM Item' : 'Create BOM Item' }}</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ $bomItem->exists ? route('bom-items.update', $bomItem->id) : route('bom-items.store') }}" method="POST">
                    @csrf
                    @if($bomItem->exists)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="product_id">Finished Product <span class="text-danger">*</span></label>
                                    <select name="product_id" id="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                                        <option value="">Select Finished Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ old('product_id', $bomItem->product_id) == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }} ({{ $product->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="material_product_id">Material Product <span class="text-danger">*</span></label>
                                    <select name="material_product_id" id="material_product_id" class="form-control @error('material_product_id') is-invalid @enderror" required>
                                        <option value="">Select Material</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ old('material_product_id', $bomItem->material_product_id) == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }} ({{ $product->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('material_product_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="quantity_required">Quantity Required <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity_required" id="quantity_required" class="form-control @error('quantity_required') is-invalid @enderror"
                                           value="{{ old('quantity_required', $bomItem->quantity_required ?? 1) }}" required>
                                    @error('quantity_required')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="unit">Unit</label>
                                    <input type="text" name="unit" id="unit" class="form-control @error('unit') is-invalid @enderror"
                                           value="{{ old('unit', $bomItem->unit ?? 'pcs') }}">
                                    @error('unit')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="estimated_cost">Estimated Cost</label>
                                    <input type="number" name="estimated_cost" id="estimated_cost" class="form-control @error('estimated_cost') is-invalid @enderror"
                                           value="{{ old('estimated_cost', $bomItem->estimated_cost ?? 0) }}" step="0.01">
                                    @error('estimated_cost')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <a href="{{ route('bom-items.index') }}" class="btn btn-secondary float-right">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
