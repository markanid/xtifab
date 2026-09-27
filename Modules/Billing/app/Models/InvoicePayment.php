<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['payment_date' => 'date'];
    }
}
