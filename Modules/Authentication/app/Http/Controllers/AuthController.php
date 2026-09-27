<?php

namespace Modules\Authentication\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function form()
    {
        return response()
            ->view('authentication::login')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt(array_merge($credentials, ['status' => 'active']), $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'The supplied credentials are invalid.']);
        }

        $request->session()->regenerate();
        $user = $request->user()->load('company');

        if ((! $user->isStaff() && ! $user->isCustomer()) || ($user->isCustomer() && $user->company?->status !== 'active')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages(['email' => 'This account cannot access the portal.']);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->route($user->isStaff() ? 'staff.dashboard' : 'customer.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
