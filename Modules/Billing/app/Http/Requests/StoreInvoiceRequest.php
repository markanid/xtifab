<?php

namespace Modules\Billing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return ['project_id' => ['required', 'exists:projects,id'], 'invoice_date' => ['required', 'date'], 'due_date' => ['required', 'date', 'after_or_equal:invoice_date'], 'currency' => ['required', 'string', 'size:3'], 'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'], 'discount_amount' => ['nullable', 'numeric', 'min:0'], 'status' => ['required', 'in:draft,issued'], 'items' => ['required', 'array', 'min:1'], 'items.*.description' => ['required', 'string'], 'items.*.quantity' => ['required', 'numeric', 'gt:0'], 'items.*.unit' => ['required', 'string'], 'items.*.unit_price' => ['required', 'numeric', 'min:0']];
    }
}
