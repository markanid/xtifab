<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
    <li class="nav-item">
        <a href="{{ route('staff.dashboard') }}" class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('staff.companies.index') }}" class="nav-link {{ request()->routeIs('staff.companies.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-building"></i><p>Companies</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('staff.customers.index') }}" class="nav-link {{ request()->routeIs('staff.customers.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i><p>Customer Users</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('staff.projects.index') }}" class="nav-link {{ request()->routeIs('staff.projects.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-project-diagram"></i><p>Projects</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('staff.invoices.index') }}" class="nav-link {{ request()->routeIs('staff.invoices.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-invoice-dollar"></i><p>Billing</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="nav-icon far fa-bell"></i><p>Notifications</p>
        </a>
    </li>
</ul>
