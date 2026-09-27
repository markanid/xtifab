<aside class="main-sidebar sidebar-light-orange elevation-4">
    <a href="{{ route(auth()->user()->isStaff() ? 'staff.dashboard' : 'customer.dashboard') }}" class="brand-link bg-white">
        <img src="{{asset('admin-assets/dist/img/logo.png')}}" alt="Logo" class="brand-image" style="width: 160px; height: 35px; display: block; margin: -1.5px 19px; padding: 0px;">
    </a>
    <div class="sidebar">
        <!-- <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image"><i class="fas fa-user-circle fa-2x text-secondary"></i></div>
            <div class="info">
                <span class="d-block">{{ auth()->user()->name }}</span>
                <small class="text-muted">{{ auth()->user()->company?->company_name ?? 'XT Tab' }}</small>
            </div>
        </div> -->
        <nav class="mt-2">
            @include(auth()->user()->isStaff() ? 'portal.partials.sidebar-staff' : 'portal.partials.sidebar-customer')
        </nav>
    </div>
</aside>
