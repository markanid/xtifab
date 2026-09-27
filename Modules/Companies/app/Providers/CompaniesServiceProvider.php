<?php

namespace Modules\Companies\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class CompaniesServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Companies';

    protected string $nameLower = 'companies';

    public function boot(): void
    {
        $this->loadRoutesFrom(module_path($this->name, 'routes/web.php'));
        $this->loadViewsFrom(module_path($this->name, 'resources/views'), 'companies');
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }
}
