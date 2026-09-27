<?php

namespace Modules\Billing\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Invoice;

class InvoiceService
{
    public function create(array $data, User $user): Invoice
    {
        return DB::transaction(function () use ($data, $user) {
            $totals = $this->calculateTotals($data);
            $invoice = Invoice::create(array_merge(collect($data)->except('items')->all(), [
                'invoice_number' => $this->nextNumber(), 'created_by' => $user->id,
            ], $totals));
            foreach ($data['items'] as $position => $item) {
                $invoice->items()->create(array_merge($item, ['line_total' => round($item['quantity'] * $item['unit_price'], 2), 'sort_order' => $position]));
            }

            return $invoice;
        });
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice->update(array_merge(
                collect($data)->except('items')->all(),
                $this->calculateTotals($data)
            ));
            $invoice->items()->delete();
            foreach ($data['items'] as $position => $item) {
                $invoice->items()->create(array_merge($item, [
                    'line_total' => round($item['quantity'] * $item['unit_price'], 2),
                    'sort_order' => $position,
                ]));
            }

            return $invoice->refresh();
        });
    }

    public function recalculatePaymentStatus(Invoice $invoice): void
    {
        $paid = (float) $invoice->payments()->sum('amount');
        $status = $paid <= 0 ? 'unpaid' : ($paid >= (float) $invoice->total_amount ? 'paid' : 'partially_paid');
        $invoice->forceFill(['payment_status' => $status, 'paid_at' => $status === 'paid' ? now() : null])->save();
    }

    private function nextNumber(): string
    {
        return 'INV-'.now()->year.'-'.str_pad((string) (Invoice::withTrashed()->whereYear('created_at', now()->year)->count() + 1), 5, '0', STR_PAD_LEFT);
    }

    private function calculateTotals(array $data): array
    {
        $subtotalCents = collect($data['items'])->sum(
            fn ($item) => (int) round($item['quantity'] * $item['unit_price'] * 100)
        );
        $discountCents = (int) round(($data['discount_amount'] ?? 0) * 100);
        $taxCents = (int) round($subtotalCents * (($data['tax_percentage'] ?? 0) / 100));

        return [
            'subtotal' => $subtotalCents / 100,
            'tax_amount' => $taxCents / 100,
            'total_amount' => max(0, $subtotalCents + $taxCents - $discountCents) / 100,
        ];
    }
}
