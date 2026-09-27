<?php

namespace Modules\Authentication\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCompanyIsActive
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->isCustomer() && $request->user()->company?->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->withErrors(['email' => 'Your company account is not active.']);
        }

        return $next($request);
    }
}
