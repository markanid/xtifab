<?php

namespace Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Notifications\Notifications\PortalNotification;
use Modules\Projects\Http\Requests\StoreProjectRequest;
use Modules\Projects\Http\Requests\UpdateProjectRequest;
use Modules\Projects\Models\Project;
use Modules\Projects\Services\LaravelProjectFileStorage;
use Modules\Projects\Services\ProjectWorkflowService;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::visibleTo($request->user())->with('company')
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('projects::projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects::projects.form', ['project' => new Project]);
    }

    public function store(StoreProjectRequest $request, LaravelProjectFileStorage $storage)
    {
        $project = DB::transaction(function () use ($request, $storage) {
            $project = Project::create(array_merge($request->safe()->except('drawings'), [
                'project_number' => 'PRJ-'.now()->year.'-'.str_pad((string) (Project::withTrashed()->whereYear('created_at', now()->year)->count() + 1), 5, '0', STR_PAD_LEFT),
                'company_id' => $request->user()->company_id,
                'created_by' => $request->user()->id,
            ]));
            $project->activities()->create([
                'user_id' => $request->user()->id,
                'event_type' => 'project_created',
                'description' => 'Project created',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            foreach ($request->file('drawings', []) as $file) {
                $project->drawings()->create(array_merge(
                    $storage->upload($file, "projects/{$project->company_id}/{$project->id}/drawings"),
                    ['uploaded_by' => $request->user()->id, 'uploaded_at' => now()]
                ));
            }

            return $project;
        });

        User::whereIn('role', ['super_admin', 'xt_tab_user'])->where('status', 'active')->each(
            fn ($user) => $user->notify(new PortalNotification(
                'New project submitted',
                "{$project->project_number} was submitted.",
                route('staff.projects.show', $project)
            ))
        );

        return redirect()->route('customer.projects.show', $project)->with('success', 'Project submitted.');
    }

    public function show(Request $request, Project $project)
    {
        $this->authorizeTenant($request, $project);
        $project->load(['company', 'drawings', 'deliverables', 'invoices.items', 'activities']);

        return view('projects::projects.show', compact('project'));
    }

    public function edit(Request $request, Project $project)
    {
        $this->authorizeTenant($request, $project);
        abort_if(in_array($project->status, ['completed', 'cancelled'], true), 403);
        abort_if($request->user()->isCustomer() && $project->status !== 'submitted', 403);

        return view('projects::projects.form', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $data = $request->validated();
        if ($request->user()->isCustomer()) {
            unset($data['internal_notes']);
        }
        $old = $project->only(array_keys($data));
        $project->update($data);
        $project->activities()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'project_updated',
            'description' => 'Project details updated',
            'old_values' => $old,
            'new_values' => $project->only(array_keys($data)),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route(
            $request->user()->isStaff() ? 'staff.projects.show' : 'customer.projects.show',
            $project
        )->with('success', 'Project updated.');
    }

    public function destroy(Request $request, Project $project)
    {
        $this->authorizeTenant($request, $project);
        if ($request->user()->isCustomer() && $project->status !== 'submitted') {
            abort(403);
        }
        if ($request->user()->isStaff() && in_array($project->status, ['completed'], true)) {
            return back()->withErrors(['project' => 'Completed projects must remain available for audit.']);
        }
        if ($project->invoices()->exists()) {
            return back()->withErrors(['project' => 'A project linked to invoices cannot be archived.']);
        }

        $project->delete();

        return redirect()->route(
            $request->user()->isStaff() ? 'staff.projects.index' : 'customer.projects.index'
        )->with('success', 'Project archived.');
    }

    public function updateStatus(Request $request, Project $project, ProjectWorkflowService $service)
    {
        $data = $request->validate([
            'status' => ['required', 'in:submitted,under_review,in_progress,on_hold,completed,cancelled'],
        ]);
        $service->changeStatus($project, $data['status'], $request->user());

        return back()->with('success', 'Project status updated.');
    }

    private function authorizeTenant(Request $request, Project $project): void
    {
        abort_if($request->user()->isCustomer() && $project->company_id !== $request->user()->company_id, 403);
    }
}
