<?php

namespace Modules\Billing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Companies\Models\Company;
use Modules\Projects\Models\Project;

class Invoice extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['invoice_date' => 'date', 'due_date' => 'date', 'approved_at' => 'datetime', 'paid_at' => 'datetime'];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->isCustomer() ? $query->where('company_id', $user->company_id) : $query;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function getAmountPaidAttribute(): string
    {
        return number_format((float) $this->payments()->sum('amount'), 2, '.', '');
    }

    public function getBalanceDueAttribute(): string
    {
        return number_format(max(0, (float) $this->total_amount - (float) $this->payments()->sum('amount')), 2, '.', '');
    }
}
