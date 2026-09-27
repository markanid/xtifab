<?php

namespace Modules\Projects\Providers;

use Modules\Projects\Contracts\ProjectFileStorageInterface;
use Modules\Projects\Services\LaravelProjectFileStorage;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ProjectsServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Projects';

    protected string $nameLower = 'projects';

    public function register(): void
    {
        parent::register();
        $this->mergeConfigFrom(module_path($this->name, 'config/config.php'), 'projects');
        $this->app->bind(ProjectFileStorageInterface::class, LaravelProjectFileStorage::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(module_path($this->name, 'routes/web.php'));
        $this->loadViewsFrom(module_path($this->name, 'resources/views'), 'projects');
    }
}
