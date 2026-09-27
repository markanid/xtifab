<?php

namespace Modules\Projects\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Billing\Models\Invoice;
use Modules\Companies\Models\Company;
use Modules\Deliverables\Models\Deliverable;

class Project extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['required_delivery_date' => 'date', 'estimated_start_date' => 'date', 'estimated_completion_date' => 'date', 'actual_completion_date' => 'date'];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->isCustomer() ? $query->where('company_id', $user->company_id) : $query;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function drawings()
    {
        return $this->hasMany(ProjectDrawing::class);
    }

    public function deliverables()
    {
        return $this->hasMany(Deliverable::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'submitted' => 'badge-info',
            'under_review' => 'badge-warning',
            'in_progress' => 'badge-primary',
            'on_hold' => 'badge-secondary',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function activities()
    {
        return $this->hasMany(ProjectActivity::class)->latest();
    }
}
