<?php

namespace Modules\Authentication\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Authentication\Http\Requests\UpdateAccountPasswordRequest;

class AccountPasswordController extends Controller
{
    public function edit()
    {
        return view('authentication::account.password');
    }

    public function update(UpdateAccountPasswordRequest $request)
    {
        $user = $request->user();
        $user->update([
            'password' => $request->password,
            'must_change_password' => false,
        ]);
        $request->session()->regenerate();

        return redirect()
            ->route($user->isStaff() ? 'staff.dashboard' : 'customer.dashboard')
            ->with('success', 'Your password has been changed.');
    }
}
