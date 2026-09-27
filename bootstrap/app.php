<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Modules\Authentication\Http\Middleware\EnsureCompanyIsActive;
use Modules\Authentication\Http\Middleware\EnsurePortalRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'portal.role' => EnsurePortalRole::class,
            'company.active' => EnsureCompanyIsActive::class,
        ]);

        $middleware->redirectGuestsTo(fn (): string => route('login'));

        $middleware->redirectUsersTo(function (Request $request): string {
            return route($request->user()->isStaff() ? 'staff.dashboard' : 'customer.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
