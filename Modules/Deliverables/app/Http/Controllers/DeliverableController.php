<?php

namespace Modules\Deliverables\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Deliverables\Models\Deliverable;
use Modules\Projects\Models\Project;
use Modules\Projects\Services\LaravelProjectFileStorage;

class DeliverableController extends Controller
{
    public function store(Request $request, Project $project, LaravelProjectFileStorage $storage)
    {
        $data = $request->validate([
            'deliverable_type' => ['required', 'in:material_list,ifc_model,bolt_list,drawing_log,other'],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'file', 'max:20480'],
            'is_visible_to_customer' => ['nullable', 'boolean'],
        ]);
        $stored = $storage->upload(
            $request->file('file'),
            "projects/{$project->company_id}/{$project->id}/deliverables"
        );
        unset($data['file']);
        $project->deliverables()->create(array_merge($stored, $data, [
            'uploaded_by' => $request->user()->id,
            'uploaded_at' => now(),
            'is_visible_to_customer' => $request->boolean('is_visible_to_customer'),
        ]));

        return back()->with('success', 'Deliverable uploaded.');
    }

    public function download(Request $request, Deliverable $deliverable, LaravelProjectFileStorage $storage)
    {
        abort_if(
            $request->user()->isCustomer() && $deliverable->project->company_id !== $request->user()->company_id,
            403
        );
        abort_if($request->user()->isCustomer() && ! $deliverable->is_visible_to_customer, 404);
        abort_unless($storage->exists($deliverable->disk, $deliverable->file_path), 404);

        return $storage->download($deliverable->disk, $deliverable->file_path, $deliverable->original_name);
    }
}
