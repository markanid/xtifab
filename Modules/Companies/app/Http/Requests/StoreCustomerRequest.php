<?php

namespace Modules\Companies\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return ['company_id' => ['required', 'exists:companies,id'], 'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'unique:users,email'], 'phone' => ['nullable', 'string'], 'password' => ['required', 'string', 'min:10'], 'status' => ['required', 'in:active,inactive']];
    }
}
