@extends('portal.layout')
@section('title', $project->exists ? 'Edit Project' : 'Create Project')
@section('content')
@php($portal = auth()->user()->isStaff() ? 'staff' : 'customer')
<form method="post" enctype="multipart/form-data" action="{{ $project->exists ? route($portal.'.projects.update', $project) : route('customer.projects.store') }}">@csrf
@if($project->exists) @method('PUT') @endif
<div class="card card-orange"><div class="card-header"><h3 class="card-title">Project Details</h3><div class="card-tools"><a href="{{ $project->exists ? route($portal.'.projects.show', $project) : route('customer.projects.index') }}" class="btn btn-flat btn-sm" style="background-color:#000;color:#fff;"><i class="fas fa-arrow-alt-circle-left mr-1"></i>Back</a></div></div><div class="card-body"><div class="row">
    <div class="form-group col-md-6"><label>Project ID</label><input class="form-control" value="{{ $project->project_number }}" readonly></div>
    @foreach(['project_name' => 'Project Name', 'project_location' => 'Location', 'required_delivery_date' => 'Required Delivery Date'] as $name => $label)
        <div class="form-group col-md-6"><label class="{{ $name === 'project_name' ? 'required' : '' }}">{{ $label }}</label><input class="form-control @error($name) is-invalid @enderror" name="{{ $name }}" type="{{ $name === 'required_delivery_date' ? 'date' : 'text' }}" value="{{ old($name, $name === 'required_delivery_date' ? $project->{$name}?->format('Y-m-d') : $project->{$name}) }}" {{ $name === 'project_name' ? 'required' : '' }}>@error($name)<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
    @endforeach
    <div class="form-group col-12"><label>Description</label><textarea class="form-control" name="project_description" rows="4">{{ old('project_description', $project->project_description) }}</textarea></div>
    @if(auth()->user()->isStaff())<div class="form-group col-12"><label>Internal Notes</label><textarea class="form-control" name="internal_notes">{{ old('internal_notes', $project->internal_notes) }}</textarea></div>@endif
    @unless($project->exists)<div class="form-group col-12"><label>Drawing</label><div class="custom-file"><input type="file" name="drawings[]" multiple class="custom-file-input" id="drawings"><label class="custom-file-label" for="drawings">Choose files</label></div><small class="form-text text-muted">Accepted: documents, spreadsheets, images, ZIP, RAR and 7Z. Maximum 20 MB each.</small></div>@endunless
</div></div><div class="card-footer d-flex justify-content-center align-items-center" style="gap:1rem;"><button class="btn btn-flat bg-orange"><i class="fas fa-save mr-1"></i> {{ $project->exists ? 'Update Project' : 'Submit Project' }}</button><button type="reset" class="btn btn-default btn-flat"><i class="fas fa-undo-alt mr-1"></i>Reset</button></div></div>
</form>
@endsection
