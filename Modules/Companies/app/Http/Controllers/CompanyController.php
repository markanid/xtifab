<?php

namespace Modules\Companies\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Companies\Http\Requests\StoreCompanyRequest;
use Modules\Companies\Http\Requests\UpdateCompanyRequest;
use Modules\Companies\Models\Company;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount(['users', 'projects'])
            ->latest()
            ->get();

        return view('companies::companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies::companies.form', ['company' => new Company]);
    }

    public function store(StoreCompanyRequest $request)
    {
        Company::create(array_merge($request->validated(), [
            'company_code' => 'COM-'.str_pad((string) (Company::withTrashed()->count() + 1), 5, '0', STR_PAD_LEFT),
            'created_by' => $request->user()->id,
        ]));

        return redirect()->route('staff.companies.index')->with('success', 'Company created.');
    }

    public function show(Company $company)
    {
        $company->loadCount(['users', 'projects', 'invoices']);

        return view('companies::companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('companies::companies.form', compact('company'));
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company->update($request->validated());

        return redirect()->route('staff.companies.show', $company)->with('success', 'Company updated.');
    }

    public function destroy(Company $company)
    {
        if ($company->projects()->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
            return back()->withErrors(['company' => 'Archive or complete active projects before deleting this company.']);
        }

        $company->update(['status' => 'inactive']);
        $company->delete();

        return redirect()->route('staff.companies.index')->with('success', 'Company archived.');
    }
}
