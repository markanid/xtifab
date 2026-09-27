<?php

namespace Modules\Authentication\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Authentication\Http\Requests\StoreStaffUserRequest;
use Modules\Authentication\Http\Requests\UpdateStaffUserRequest;

class StaffUserController extends Controller
{
    public function index()
    {
        $staffUsers = User::whereIn('role', ['super_admin', 'xt_tab_user'])
            ->latest()
            ->get();

        return view('authentication::staff-users.index', compact('staffUsers'));
    }

    public function create()
    {
        return view('authentication::staff-users.form', [
            'staffUser' => new User,
        ]);
    }

    public function store(StoreStaffUserRequest $request)
    {
        User::create(array_merge($request->safe()->except('password'), [
            'company_id' => null,
            'password' => Hash::make($request->password),
            'role' => 'xt_tab_user',
            'must_change_password' => true,
            'created_by' => $request->user()->id,
        ]));

        return redirect()->route('staff.staff-users.index')
            ->with('success', 'Staff account created. Share the temporary password securely.');
    }

    public function edit(User $staffUser)
    {
        $this->ensureManagedStaff($staffUser);

        return view('authentication::staff-users.form', compact('staffUser'));
    }

    public function update(UpdateStaffUserRequest $request, User $staffUser)
    {
        $this->ensureManagedStaff($staffUser);
        $data = $request->safe()->except('password');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['must_change_password'] = true;
        }

        $staffUser->update($data);

        return redirect()->route('staff.staff-users.index')
            ->with('success', 'Staff account updated.');
    }

    public function toggleStatus(User $staffUser)
    {
        $this->ensureManagedStaff($staffUser);
        $staffUser->update([
            'status' => $staffUser->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Staff login status updated.');
    }

    private function ensureManagedStaff(User $staffUser): void
    {
        abort_unless($staffUser->role === 'xt_tab_user', 404);
    }
}
