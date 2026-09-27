<?php

namespace Modules\Projects\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Notifications\Notifications\PortalNotification;
use Modules\Projects\Models\Project;

class ProjectWorkflowService
{
    public function changeStatus(Project $project, string $status, User $actor): Project
    {
        return DB::transaction(function () use ($project, $status, $actor) {
            $old = $project->status;
            if ($old === $status) {
                return $project;
            }
            $project->forceFill(['status' => $status, 'actual_completion_date' => $status === 'completed' ? now()->toDateString() : null])->save();
            $project->activities()->create(['user_id' => $actor->id, 'event_type' => 'status_changed',
                'description' => "Status changed from {$old} to {$status}", 'old_values' => ['status' => $old],
                'new_values' => ['status' => $status], 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);
            $project->company->users()->where('status', 'active')->each(fn ($user) => $user->notify(
                new PortalNotification($status === 'completed' ? 'Project completed' : 'Project status updated',
                    "{$project->project_number} is now ".str_replace('_', ' ', $status),
                    route('customer.projects.show', $project))
            ));

            return $project->refresh();
        });
    }
}
