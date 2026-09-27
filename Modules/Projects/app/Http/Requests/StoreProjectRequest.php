<?php

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() ?? false;
    }

    public function rules(): array
    {
        return ['project_name' => ['required', 'string', 'max:255'], 'client_reference' => ['nullable', 'string', 'max:255'], 'project_description' => ['nullable', 'string'], 'project_location' => ['nullable', 'string'], 'contact_person' => ['nullable', 'string'], 'required_delivery_date' => ['nullable', 'date', 'after_or_equal:today'], 'priority' => ['required', 'in:low,normal,high,urgent'], 'customer_notes' => ['nullable', 'string'], 'drawings' => ['nullable', 'array'], 'drawings.*' => ['file', 'mimes:pdf,xls,xlsx,csv', 'max:'.config('projects.files.max_kb', 20480)]];
    }
}
