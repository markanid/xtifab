@extends('portal.layout')
@section('title', 'Companies')
@section('content')
<div class="card card-outline card-orange">
    <div class="card-header">
        <h3 class="card-title">Company List</h3>
        <a class="btn btn-flat btn-sm float-right bg-orange" href="{{ route('staff.companies.create') }}"><i class="fas fa-plus mr-1"></i> Create Company</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover portal-data-table">
            <thead><tr><th>Code</th><th>Company</th><th>Contact</th><th>Status</th><th>Users</th><th>Projects</th><th class="text-right no-export">Actions</th></tr></thead>
            <tbody>
            @foreach($companies as $company)
                <tr><td>{{ $company->company_code }}</td><td><a href="{{ route('staff.companies.show', $company) }}">{{ $company->company_name }}</a></td><td>{{ $company->contact_person }}<small class="d-block text-muted">{{ $company->email }}</small></td><td><span class="badge badge-{{ $company->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($company->status) }}</span></td><td>{{ $company->users_count }}</td><td>{{ $company->projects_count }}</td><td class="text-right"><div class="btn-group"><a href="{{ route('staff.companies.edit', $company) }}" class="btn btn-primary btn-flat btn-sm" title="Edit"><i class="fas fa-edit"></i></a><button type="button" class="btn btn-default btn-flat btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown"><span class="sr-only">Actions</span></button><div class="dropdown-menu dropdown-menu-right"><form method="post" action="{{ route('staff.companies.destroy', $company) }}" data-confirm="Archive {{ $company->company_name }}? Active projects prevent this action.">@csrf @method('DELETE')<button class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i>Archive</button></form></div></div></td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
