@extends('portal.layout')
@section('title', 'Change Password')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <form method="post" action="{{ route('account.password.update') }}">
            @csrf
            @method('PUT')

            <div class="card card-outline card-orange">
                <div class="card-header">
                    <h3 class="card-title">Update Your Password</h3>
                    <div class="card-tools">
                        <a href="{{ route(auth()->user()->isStaff() ? 'staff.dashboard' : 'customer.dashboard') }}" class="btn btn-flat btn-sm" style="background-color:#000;color:#fff;">
                            <i class="fas fa-arrow-alt-circle-left mr-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(auth()->user()->must_change_password)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            You are currently using a temporary password. Change it before continuing to use your account.
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Current Password</label>
                        <div class="input-group">
                            <input class="form-control @error('current_password') is-invalid @enderror" type="password" name="current_password" autocomplete="current-password" required autofocus>
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-lock"></span></div>
                            </div>
                        </div>
                        @error('current_password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>New Password</label>
                        <div class="input-group">
                            <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" autocomplete="new-password" required>
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-key"></span></div>
                            </div>
                        </div>
                        @error('password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        <small class="form-text text-muted">Use at least 10 characters.</small>
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <div class="input-group">
                            <input class="form-control" type="password" name="password_confirmation" autocomplete="new-password" required>
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-key"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-center align-items-center" style="gap:1rem;">
                    <button type="submit" class="btn btn-flat bg-orange">
                        <i class="fas fa-key mr-1"></i>Update Password
                    </button>
                    <button type="reset" class="btn btn-default btn-flat">
                        <i class="fas fa-undo-alt mr-1"></i>Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
