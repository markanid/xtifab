<?php

namespace Modules\Companies\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Companies\Http\Requests\StoreCustomerRequest;
use Modules\Companies\Http\Requests\UpdateCustomerRequest;
use Modules\Companies\Models\Company;

class CustomerUserController extends Controller
{
    public function index()
    {
        $customers = User::where('role', 'customer_user')->with('company')->latest()->get();

        return view('companies::customers.index', compact('customers'));
    }

    public function create()
    {
        return view('companies::customers.form', [
            'customer' => new User,
            'companies' => Company::where('status', 'active')->orderBy('company_name')->get(),
        ]);
    }

    public function show(User $customer)
    {
        abort_unless($customer->isCustomer(), 404);
        $customer->load('company');

        return view('companies::customers.show', compact('customer'));
    }

    public function edit(User $customer)
    {
        abort_unless($customer->isCustomer(), 404);

        return view('companies::customers.form', [
            'customer' => $customer,
            'companies' => Company::where('status', 'active')->orderBy('company_name')->get(),
        ]);
    }

    public function store(StoreCustomerRequest $request)
    {
        User::create(array_merge($request->safe()->except('password'), [
            'password' => Hash::make($request->password),
            'role' => 'customer_user',
            'must_change_password' => true,
            'created_by' => $request->user()->id,
        ]));

        return redirect()->route('staff.customers.index')
            ->with('success', 'Customer login created. Share the temporary password securely; it will not be shown again.');
    }

    public function update(UpdateCustomerRequest $request, User $customer)
    {
        abort_unless($customer->isCustomer(), 404);
        $data = $request->safe()->except('password');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['must_change_password'] = true;
        }
        $customer->update($data);

        return redirect()->route('staff.customers.show', $customer)->with('success', 'Customer user updated.');
    }

    public function toggleStatus(User $customer)
    {
        abort_unless($customer->isCustomer(), 404);
        $customer->update(['status' => $customer->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'Customer login status updated.');
    }
}
