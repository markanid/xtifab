<?php

namespace Modules\Billing\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class BillingServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Billing';

    protected string $nameLower = 'billing';

    public function boot(): void
    {
        $this->loadRoutesFrom(module_path($this->name, 'routes/web.php'));
        $this->loadViewsFrom(module_path($this->name, 'resources/views'), 'billing');
    }
}
