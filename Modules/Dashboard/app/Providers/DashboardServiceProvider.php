<?php

namespace Modules\Dashboard\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class DashboardServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Dashboard';

    protected string $nameLower = 'dashboard';

    public function boot(): void
    {
        $this->loadRoutesFrom(module_path($this->name, 'routes/web.php'));
        $this->loadViewsFrom(module_path($this->name, 'resources/views'), 'dashboard');
    }
}
