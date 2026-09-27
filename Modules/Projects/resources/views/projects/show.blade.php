@extends('portal.layout')
@section('title', $project->project_number)
@section('content')
<div class="card card-outline card-orange">
    <div class="card-header"><h3 class="card-title">{{ $project->project_name }}</h3><div class="card-tools">@php($portal = auth()->user()->isStaff() ? 'staff' : 'customer') @if(! in_array($project->status, ['completed', 'cancelled']) && (auth()->user()->isStaff() || $project->status === 'submitted') && auth()->user()->role !== 'super_admin')<a href="{{ route($portal.'.projects.edit', $project) }}" class="btn btn-primary btn-flat btn-sm mr-2"><i class="fas fa-edit mr-1"></i>Edit</a>@endif<span class="badge {{ $project->status_badge_class }}">{{ $project->status_label }}</span></div></div>
    <div class="card-body"><dl class="row"><dt class="col-md-2">Project ID</dt><dd class="col-md-4">{{ $project->project_number }}</dd><dt class="col-md-2">Company</dt><dd class="col-md-4">{{ $project->company->company_name }}</dd><dt class="col-md-2">Location</dt><dd class="col-md-4">{{ $project->project_location ?: '-' }}</dd><dt class="col-md-2">Required Delivery Date</dt><dd class="col-md-4">{{ $project->required_delivery_date?->format('d M Y') ?: '-' }}</dd><dt class="col-md-2">Description</dt><dd class="col-md-10">{{ $project->project_description ?: '-' }}</dd></dl>
    @if(auth()->user()->isStaff())<form class="form-inline border-top pt-3" method="post" action="{{ route('staff.projects.status', $project) }}">@csrf @method('PATCH')<label class="mr-2">Workflow status</label><select class="form-control mr-2" name="status">@foreach(\Modules\Projects\Models\Project::WORKFLOW_STATUSES as $status => $label)<option value="{{ $status }}" @selected($project->status === $status)>{{ $label }}</option>@endforeach</select><button class="btn btn-flat bg-orange">Update</button></form>@endif
    </div>
</div>
<div class="row">
    <div class="col-12">@include('projects::projects.partials.drawing-explorer')</div>
</div>
<div class="row">
    <div class="col-12">@include('portal.partials.file-explorer', ['explorerKey' => 'deliverable'])</div>
</div>
<div class="card"><div class="card-header"><h3 class="card-title">Activity</h3></div><div class="card-body"><div class="timeline">@forelse($project->activities as $activity)<div><i class="fas fa-history bg-blue"></i><div class="timeline-item"><span class="time"><i class="far fa-clock"></i> {{ $activity->created_at->diffForHumans() }}</span><h3 class="timeline-header">{{ ucwords(str_replace('_', ' ', $activity->event_type)) }}</h3><div class="timeline-body">{{ $activity->description }}</div></div></div>@empty<p class="text-muted">No activity.</p>@endforelse</div></div></div>
@endsection
