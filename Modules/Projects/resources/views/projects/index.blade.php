@extends('portal.layout')
@section('title', auth()->user()->isStaff() ? 'All Projects' : 'My Projects')
@section('content')
<div class="card card-outline card-orange">
    <div class="card-header">
        <form class="form-inline">
            <select class="form-control form-control-sm mr-2" name="status"><option value="">All statuses</option>@foreach(\Modules\Projects\Models\Project::WORKFLOW_STATUSES as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select>
            <button class="btn btn-secondary btn-flat btn-sm"><i class="fas fa-filter mr-1"></i> Filter</button>
            @if(auth()->user()->isCustomer())<a class="btn btn-flat btn-sm ml-auto bg-orange" href="{{ route('customer.projects.create') }}"><i class="fas fa-plus mr-1"></i> Create Project</a>@endif
        </form>
    </div>
    <div class="card-body"><table class="table table-bordered table-hover portal-data-table"><thead><tr><th>Project ID</th><th>Project</th><th>Company</th><th>Status</th><th>Required</th><th class="text-right no-export">Actions</th></tr></thead><tbody>
        @foreach($projects as $project)
            @php($portal = auth()->user()->isStaff() ? 'staff' : 'customer')
            @php($canModify = ! in_array($project->status, ['completed', 'cancelled']) && (auth()->user()->isStaff() || $project->status === 'submitted'))
            @php($canEdit = $canModify && auth()->user()->role !== 'super_admin')
            <tr><td><a href="{{ route($portal.'.projects.show', $project) }}">{{ $project->project_number }}</a></td><td>{{ $project->project_name }}</td><td>{{ $project->company->company_name }}</td><td><span class="badge {{ $project->status_badge_class }}">{{ $project->status_label }}</span></td><td>{{ $project->required_delivery_date?->format('d M Y') ?: '-' }}</td><td class="text-right"><div class="btn-group">@if($canEdit)<a href="{{ route($portal.'.projects.edit', $project) }}" class="btn btn-primary btn-flat btn-sm"><i class="fas fa-edit"></i></a>@endif @if($canModify)<form method="post" action="{{ route($portal.'.projects.destroy', $project) }}" data-confirm="Archive project {{ $project->project_number }}? This is unavailable when invoices exist.">@csrf @method('DELETE')<button class="btn btn-danger btn-flat btn-sm"><i class="fas fa-trash"></i></button></form>@endif</div></td></tr>
        @endforeach
    </tbody></table></div>
</div>
@endsection
