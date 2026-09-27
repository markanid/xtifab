<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
    <li class="nav-item"><a href="{{ route('customer.dashboard') }}" class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
    <li class="nav-item">
        <a href="{{ route('customer.projects.index') }}" class="nav-link {{ request()->routeIs('customer.projects.*') ? 'active' : '' }}"><i class="nav-icon fas fa-project-diagram"></i><p>My Projects</p></a>
    </li>
    <li class="nav-item">
        <a href="{{ route('customer.invoices.index') }}" class="nav-link {{ request()->routeIs('customer.invoices.*') ? 'active' : '' }}"><i class="nav-icon fas fa-file-invoice-dollar"></i><p>Billing</p></a>
    </li>
    <li class="nav-item"><a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}"><i class="nav-icon far fa-bell"></i><p>Notifications</p></a></li>
</ul>
