@extends('portal.layout')
@section('title', $staffUser->exists ? 'Edit Staff User' : 'Create Staff User')
@section('content')
<form method="post" action="{{ $staffUser->exists ? route('staff.staff-users.update', $staffUser) : route('staff.staff-users.store') }}">
    @csrf
    @if($staffUser->exists)
        @method('PUT')
    @endif

    <div class="card card-orange">
        <div class="card-header">
            <h3 class="card-title">Staff Login Details</h3>
            <div class="card-tools">
                <a href="{{ route('staff.staff-users.index') }}" class="btn btn-flat btn-sm" style="background-color:#000;color:#fff;">
                    <i class="fas fa-arrow-alt-circle-left mr-1"></i>Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Name</label>
                    <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $staffUser->name) }}" required>
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Email</label>
                    <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $staffUser->email) }}" required>
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Phone</label>
                    <input class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $staffUser->phone) }}">
                    @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Temporary Password</label>
                    @if($staffUser->exists)
                        <small class="text-muted">(leave blank to keep the current password)</small>
                    @endif
                    <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" {{ $staffUser->exists ? '' : 'required' }}>
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Status</label>
                    <select class="form-control" name="status">
                        <option value="active" @selected(old('status', $staffUser->status ?: 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $staffUser->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle mr-1"></i>
                Staff accounts can manage companies, customer users, projects, deliverables and billing. The temporary password is not displayed again.
            </div>
        </div>
        <div class="card-footer d-flex justify-content-center align-items-center" style="gap:1rem;">
            <button class="btn btn-flat bg-orange">
                <i class="fas fa-save mr-1"></i>{{ $staffUser->exists ? 'Update Staff User' : 'Create Staff User' }}
            </button>
            <button type="reset" class="btn btn-default btn-flat">
                <i class="fas fa-undo-alt mr-1"></i>Reset
            </button>
        </div>
    </div>
</form>
@endsection
