<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDrawing extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['uploaded_at' => 'datetime'];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
