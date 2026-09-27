<?php

namespace Modules\Deliverables\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class DeliverablesServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Deliverables';

    protected string $nameLower = 'deliverables';

    public function boot(): void
    {
        $this->loadRoutesFrom(module_path($this->name, 'routes/web.php'));
    }
}
