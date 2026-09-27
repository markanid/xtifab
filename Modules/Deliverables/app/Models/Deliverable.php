<?php

namespace Modules\Deliverables\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Projects\Models\Project;

class Deliverable extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['uploaded_at' => 'datetime', 'is_visible_to_customer' => 'boolean'];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
