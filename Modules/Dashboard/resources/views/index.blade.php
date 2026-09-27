@extends('portal.layout')
@section('title', auth()->user()->isStaff() ? 'Admin Dashboard' : 'Customer Dashboard')
@section('content')
@php
    $isStaff = auth()->user()->isStaff();
    $boxConfig = [
        'projects' => [
            'icon' => 'fas fa-boxes',
            'color' => 'info',
            'route' => route($isStaff ? 'staff.projects.index' : 'customer.projects.index'),
        ],
        'in_progress' => [
            'icon' => 'fas fa-tools',
            'color' => 'primary',
            'route' => route($isStaff ? 'staff.projects.index' : 'customer.projects.index', ['status' => 'in_progress']),
        ],
        'on_hold' => [
            'icon' => 'fas fa-pause-circle',
            'color' => 'warning',
            'route' => route($isStaff ? 'staff.projects.index' : 'customer.projects.index', ['status' => 'on_hold']),
        ],
        'completed' => [
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
            'route' => route($isStaff ? 'staff.projects.index' : 'customer.projects.index', ['status' => 'completed']),
        ],
        'unpaid' => [
            'icon' => 'fas fa-file-invoice-dollar',
            'color' => 'danger',
            'route' => route($isStaff ? 'staff.invoices.index' : 'customer.invoices.index'),
        ],
        'companies' => [
            'icon' => 'fas fa-building',
            'color' => 'secondary',
            'route' => route('staff.companies.index'),
        ],
    ];
@endphp
<div class="row">
    @foreach($stats as $key => $value)
        @php
            $config = $boxConfig[$key] ?? [
                'icon' => 'fas fa-chart-bar',
                'color' => 'info',
                'route' => route($isStaff ? 'staff.projects.index' : 'customer.projects.index'),
            ];
        @endphp
        <div class="col-lg-3 col-6"><div class="small-box bg-{{ $config['color'] }}"><div class="inner"><h3>{{ $value }}</h3><p>{{ ucwords(str_replace('_', ' ', $key)) }}</p></div><div class="icon"><i class="{{ $config['icon'] }}"></i></div><a href="{{ $config['route'] }}" class="small-box-footer">View details <i class="fas fa-arrow-circle-right"></i></a></div></div>
    @endforeach
</div>
<div class="card card-outline card-orange"><div class="card-header"><h3 class="card-title">Recent Projects</h3></div><div class="card-body table-responsive p-0"><table class="table table-hover"><thead><tr><th>Number</th><th>Project</th>@if(auth()->user()->isStaff())<th>Company</th>@endif<th>Status</th><th>Delivery</th></tr></thead><tbody>
    @forelse($projects as $project)<tr><td><a href="{{ route(auth()->user()->isStaff() ? 'staff.projects.show' : 'customer.projects.show', $project) }}">{{ $project->project_number }}</a></td><td>{{ $project->project_name }}</td>@if(auth()->user()->isStaff())<td>{{ $project->company->company_name }}</td>@endif<td><span class="badge {{ $project->status_badge_class }}">{{ ucwords(str_replace('_', ' ', $project->status)) }}</span></td><td>{{ $project->required_delivery_date?->format('d M Y') ?: '—' }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No projects yet.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
