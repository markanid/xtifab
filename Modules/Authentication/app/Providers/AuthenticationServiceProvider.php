<?php

namespace Modules\Authentication\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class AuthenticationServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Authentication';

    protected string $nameLower = 'authentication';

    public function boot(): void
    {
        $this->loadRoutesFrom(module_path($this->name, 'routes/web.php'));
        $this->loadViewsFrom(module_path($this->name, 'resources/views'), 'authentication');
    }
}
