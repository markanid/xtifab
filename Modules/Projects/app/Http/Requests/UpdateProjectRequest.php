<?php

namespace Modules\Projects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $user = $this->user();

        if (! $user || ! $project || in_array($project->status, ['completed', 'cancelled'], true)) {
            return false;
        }

        return $user->isStaff()
            || ($user->isCustomer() && $project->company_id === $user->company_id && $project->status === 'submitted');
    }

    public function rules(): array
    {
        return [
            'project_name' => ['required', 'string', 'max:255'],
            'client_reference' => ['nullable', 'string', 'max:255'],
            'project_description' => ['nullable', 'string'],
            'project_location' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'required_delivery_date' => ['nullable', 'date'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'customer_notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
        ];
    }
}
