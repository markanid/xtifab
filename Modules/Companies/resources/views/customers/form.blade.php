@extends('portal.layout')
@section('title', $customer->exists ? 'Edit Customer User' : 'Create Customer User')
@section('content')
<form method="post" action="{{ $customer->exists ? route('staff.customers.update', $customer) : route('staff.customers.store') }}">@csrf
    @if($customer->exists) @method('PUT') @endif
    <div class="card card-orange"><div class="card-header"><h3 class="card-title">Customer Login Details</h3><div class="card-tools"><a href="{{ $customer->exists ? route('staff.customers.show', $customer) : route('staff.customers.index') }}" class="btn btn-flat btn-sm" style="background-color:#000;color:#fff;"><i class="fas fa-arrow-alt-circle-left mr-1"></i>Back</a></div></div><div class="card-body"><div class="row">
        <div class="form-group col-md-6"><label class="required">Company</label><select class="form-control" name="company_id" required>@foreach($companies as $company)<option value="{{ $company->id }}" @selected(old('company_id', $customer->company_id) == $company->id)>{{ $company->company_name }}</option>@endforeach</select></div>
        @foreach(['name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'password' => 'Temporary Password'] as $name => $label)
            <div class="form-group col-md-6"><label class="{{ in_array($name, ['name', 'email']) || ($name === 'password' && ! $customer->exists) ? 'required' : '' }}">{{ $label }} @if($name === 'password' && $customer->exists)<small class="text-muted">(leave blank to keep current)</small>@endif</label><input class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" value="{{ $name === 'password' ? '' : old($name, $customer->{$name}) }}" type="{{ $name === 'password' ? 'password' : ($name === 'email' ? 'email' : 'text') }}" {{ in_array($name, ['name', 'email']) || ($name === 'password' && ! $customer->exists) ? 'required' : '' }}>@error($name)<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
        @endforeach
        <div class="form-group col-md-6"><label>Status</label><select class="form-control" name="status"><option value="active" @selected(old('status', $customer->status ?: 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $customer->status) === 'inactive')>Inactive</option></select></div>
    </div><div class="alert alert-info mb-0"><i class="fas fa-info-circle mr-1"></i> The temporary password will not be displayed again.</div></div>
    <div class="card-footer d-flex justify-content-center align-items-center" style="gap:1rem;"><button class="btn btn-flat bg-orange"><i class="fas fa-save mr-1"></i> {{ $customer->exists ? 'Update Login' : 'Create Login' }}</button><button type="reset" class="btn btn-default btn-flat"><i class="fas fa-undo-alt mr-1"></i>Reset</button></div></div>
</form>
@endsection
