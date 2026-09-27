@extends('portal.layout')
@section('title', $company->exists ? 'Edit Company' : 'Create Company')
@section('content')
<form method="post" action="{{ $company->exists ? route('staff.companies.update', $company) : route('staff.companies.store') }}">
    @csrf
    @if($company->exists) @method('PUT') @endif
    <div class="card card-orange">
        <div class="card-header">
            <h3 class="card-title">Company Details</h3>
            <div class="card-tools">
                <a href="{{ $company->exists ? route('staff.companies.show', $company) : route('staff.companies.index') }}" class="btn btn-flat btn-sm" style="background-color:#000;color:#fff;">
                    <i class="fas fa-arrow-alt-circle-left mr-1"></i>Back
                </a>
            </div>
        </div>
        <div class="card-body"><div class="row">
            @foreach(['company_name' => 'Company Name', 'contact_person' => 'Contact Person', 'email' => 'Email', 'phone' => 'Phone', 'city' => 'City', 'country' => 'Country', 'tax_number' => 'Tax Number'] as $name => $label)
                <div class="form-group col-md-6"><label class="{{ $name === 'company_name' ? 'required' : '' }}">{{ $label }}</label><input class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" value="{{ old($name, $company->{$name}) }}" {{ $name === 'company_name' ? 'required' : '' }}>@error($name)<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            @endforeach
            <div class="form-group col-md-6"><label class="required">Status</label><select class="form-control" name="status">@foreach(['active', 'inactive', 'suspended'] as $status)<option value="{{ $status }}" @selected(old('status', $company->status ?: 'active') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="form-group col-12"><label>Address</label><textarea class="form-control" name="address">{{ old('address', $company->address) }}</textarea></div>
            <div class="form-group col-12"><label>Notes</label><textarea class="form-control" name="notes">{{ old('notes', $company->notes) }}</textarea></div>
        </div></div>
        <div class="card-footer d-flex justify-content-center align-items-center" style="gap:1rem;"><button class="btn btn-flat bg-orange"><i class="fas fa-save mr-1"></i> {{ $company->exists ? 'Update Company' : 'Save Company' }}</button><button type="reset" class="btn btn-default btn-flat"><i class="fas fa-undo-alt mr-1"></i>Reset</button></div>
    </div>
</form>
@endsection
