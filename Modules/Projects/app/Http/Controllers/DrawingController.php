<?php

namespace Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectDrawing;
use Modules\Projects\Models\ProjectDrawingFolder;
use Modules\Projects\Services\LaravelProjectFileStorage;
use Modules\Projects\Services\DocumentPreviewService;

class DrawingController extends Controller
{
    public function store(Request $request, Project $project, LaravelProjectFileStorage $storage)
    {
        $this->authorizeManageProject($request, $project);

        $data = $request->validate([
            'folder_path' => ['nullable', 'string', 'max:500'],
            'drawings' => ['required', 'array', 'min:1'],
            'drawings.*' => ['file', 'max:'.config('projects.files.max_kb', 20480)],
            'relative_paths' => ['nullable', 'array'],
            'relative_paths.*' => ['nullable', 'string', 'max:1000'],
        ], $this->drawingUploadMessages(), $this->drawingUploadAttributes());
        $folderPath = $this->normalizeFolderPath($data['folder_path'] ?? '');

        foreach ($request->file('drawings', []) as $index => $file) {
            $uploadFolderPath = $this->uploadedFileFolderPath($file, $data['relative_paths'][$index] ?? null);
            $targetFolderPath = $this->normalizeFolderPath(trim($folderPath.'/'.$uploadFolderPath, '/'));
            $this->createFolderPath($project, $targetFolderPath, $request->user()->id);

            $project->drawings()->create(array_merge(
                $storage->upload($file, "projects/{$project->company_id}/{$project->id}/drawings"),
                ['folder_path' => $targetFolderPath, 'uploaded_by' => $request->user()->id, 'uploaded_at' => now()]
            ));
        }

        $project->activities()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'drawings_uploaded',
            'description' => 'Uploaded drawing files',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Files uploaded.');
    }

    public function createFolder(Request $request, Project $project)
    {
        $this->authorizeManageProject($request, $project);

        $data = $request->validate([
            'current_folder' => ['nullable', 'string', 'max:500'],
            'name' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9 _.-]+$/'],
        ]);
        $parentPath = $this->normalizeFolderPath($data['current_folder'] ?? '');
        $name = trim($data['name']);
        abort_if(in_array($name, ['.', '..'], true), 422);
        $path = $this->normalizeFolderPath(trim($parentPath.'/'.$name, '/'));

        ProjectDrawingFolder::firstOrCreate(
            ['project_id' => $project->id, 'path' => $path],
            ['created_by' => $request->user()->id, 'name' => $name, 'parent_path' => $parentPath]
        );

        return $this->redirectToProjectFolder($request, $project, $parentPath)->with('success', 'Folder created.');
    }

    public function bulk(Request $request, Project $project)
    {
        $this->authorizeManageProject($request, $project);

        $data = $request->validate([
            'operation' => ['required', 'in:copy,move,delete'],
            'current_folder' => ['nullable', 'string', 'max:500'],
            'target_folder' => ['nullable', 'string', 'max:500'],
            'file_ids' => ['nullable', 'array'],
            'file_ids.*' => ['integer'],
            'folder_ids' => ['nullable', 'array'],
            'folder_ids.*' => ['integer'],
        ]);

        $currentFolder = $this->normalizeFolderPath($data['current_folder'] ?? '');
        $targetFolder = $this->normalizeFolderPath($data['target_folder'] ?? '');
        $files = $project->drawings()->whereIn('id', $data['file_ids'] ?? [])->get();
        $folders = $project->drawingFolders()->whereIn('id', $data['folder_ids'] ?? [])->orderBy('path')->get();

        if ($files->isEmpty() && $folders->isEmpty()) {
            return back()->withErrors(['drawing' => 'Select at least one file or folder.']);
        }

        if ($data['operation'] === 'delete') {
            $this->deleteSelected($files, $folders);

            return $this->redirectToProjectFolder($request, $project, $currentFolder)->with('success', 'Selected item(s) deleted.');
        }

        if ($targetFolder !== '' && ! $project->drawingFolders()->where('path', $targetFolder)->exists()) {
            return back()->withErrors(['target_folder' => 'The destination folder no longer exists. Refresh the page and try again.']);
        }

        foreach ($folders as $folder) {
            abort_if($targetFolder === $folder->path || str_starts_with($targetFolder.'/', $folder->path.'/'), 422);
        }

        if ($data['operation'] === 'move') {
            $files->each(fn (ProjectDrawing $file) => $file->update(['folder_path' => $targetFolder]));
            $folders->each(fn (ProjectDrawingFolder $folder) => $this->moveFolder($project, $folder, $targetFolder));

            return $this->redirectToProjectFolder($request, $project, $currentFolder)->with('success', 'Selected item(s) moved.');
        }

        $files->each(fn (ProjectDrawing $file) => $this->copyFile($project, $file, $targetFolder, $request->user()->id));
        $folders->each(fn (ProjectDrawingFolder $folder) => $this->copyFolder($project, $folder, $targetFolder, $request->user()->id));

        return $this->redirectToProjectFolder($request, $project, $currentFolder)->with('success', 'Selected item(s) copied.');
    }

    public function view(Request $request, ProjectDrawing $drawing, LaravelProjectFileStorage $storage)
    {
        $this->authorizeDrawing($request, $drawing, $storage);

        if (! $request->boolean('embed')) {
            $drawing->project->activities()->create([
                'user_id' => $request->user()->id,
                'event_type' => 'drawing_viewed',
                'description' => "Viewed {$drawing->original_name}",
                'ip_address' => $request->ip(),
            ]);
        }

        $headers = [
            'Content-Disposition' => 'inline; filename="'.$drawing->original_name.'"',
        ];
        if (strtolower($drawing->extension ?: pathinfo($drawing->original_name, PATHINFO_EXTENSION)) === 'ifc') {
            $headers['Content-Type'] = 'application/octet-stream';
            $headers['X-Content-Type-Options'] = 'nosniff';
            $headers['Content-Security-Policy'] = "sandbox; default-src 'none'";
        }

        return Storage::disk($drawing->disk)->response($drawing->file_path, $drawing->original_name, $headers);
    }

    public function preview(Request $request, ProjectDrawing $drawing, LaravelProjectFileStorage $storage, DocumentPreviewService $previewService)
    {
        $this->authorizeDrawing($request, $drawing, $storage);

        $drawing->project->activities()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'drawing_viewed',
            'description' => "Viewed {$drawing->original_name}",
            'ip_address' => $request->ip(),
        ]);

        $extension = strtolower($drawing->extension ?: pathinfo($drawing->original_name, PATHINFO_EXTENSION));
        $preview = $previewService->build($drawing->disk, $drawing->file_path, $extension);

        return view('projects::drawings.preview', [
            'downloadUrl' => route('drawings.download', $drawing),
            'drawing' => $drawing,
            'preview' => $preview,
            'viewUrl' => route('drawings.view', ['drawing' => $drawing, 'embed' => 1]),
        ]);
    }

    public function download(Request $request, ProjectDrawing $drawing, LaravelProjectFileStorage $storage)
    {
        $this->authorizeDrawing($request, $drawing, $storage);

        $drawing->project->activities()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'drawing_downloaded',
            'description' => "Downloaded {$drawing->original_name}",
            'ip_address' => $request->ip(),
        ]);

        return $storage->download($drawing->disk, $drawing->file_path, $drawing->original_name);
    }

    private function authorizeDrawing(Request $request, ProjectDrawing $drawing, ?LaravelProjectFileStorage $storage = null): void
    {
        $this->authorizeProject($request, $drawing->project);
        abort_unless($storage?->exists($drawing->disk, $drawing->file_path) ?? Storage::disk($drawing->disk)->exists($drawing->file_path), 404);
    }

    private function authorizeProject(Request $request, Project $project): void
    {
        abort_if($request->user()->isCustomer() && $project->company_id !== $request->user()->company_id, 403);
    }

    private function authorizeManageProject(Request $request, Project $project): void
    {
        $this->authorizeProject($request, $project);
        abort_if(in_array($project->status, ['completed', 'cancelled'], true), 403);
        abort_if($request->user()->isCustomer() && $project->status !== 'submitted', 403);
    }

    private function normalizeFolderPath(string $path): string
    {
        $path = trim(preg_replace('#/+#', '/', str_replace('\\', '/', $path)), '/.');
        if ($path === '') {
            return '';
        }

        $segments = array_filter(explode('/', $path), fn (string $segment) => $segment !== '');
        foreach ($segments as $segment) {
            abort_if($segment === '..' || ! preg_match('/^[A-Za-z0-9 _.-]+$/', $segment), 422);
        }

        return implode('/', $segments);
    }

    private function uploadedFileFolderPath($file, ?string $relativePath = null): string
    {
        $relativePath = $relativePath ?: (method_exists($file, 'getClientOriginalPath')
            ? $file->getClientOriginalPath()
            : $file->getClientOriginalName());

        $directory = trim(str_replace('\\', '/', dirname(str_replace('\\', '/', $relativePath))), './');

        return $directory === '' || $directory === '.'
            ? ''
            : $this->normalizeFolderPath($directory);
    }

    private function createFolderPath(Project $project, string $path, int $createdBy): void
    {
        if ($path === '') {
            return;
        }

        $parentPath = '';
        foreach (explode('/', $path) as $segment) {
            $currentPath = trim($parentPath.'/'.$segment, '/');
            ProjectDrawingFolder::firstOrCreate(
                ['project_id' => $project->id, 'path' => $currentPath],
                ['created_by' => $createdBy, 'name' => $segment, 'parent_path' => $parentPath]
            );
            $parentPath = $currentPath;
        }
    }

    private function deleteSelected($files, $folders): void
    {
        $files->each(fn (ProjectDrawing $file) => $this->deleteFile($file));
        $folders->sortByDesc(fn (ProjectDrawingFolder $folder) => strlen($folder->path))
            ->each(function (ProjectDrawingFolder $folder) {
                $folder->project->drawings()
                    ->where(fn ($query) => $query->where('folder_path', $folder->path)->orWhere('folder_path', 'like', $folder->path.'/%'))
                    ->get()
                    ->each(fn (ProjectDrawing $file) => $this->deleteFile($file));

                $folder->project->drawingFolders()
                    ->where(fn ($query) => $query->where('path', $folder->path)->orWhere('path', 'like', $folder->path.'/%'))
                    ->delete();
            });
    }

    private function deleteFile(ProjectDrawing $file): void
    {
        Storage::disk($file->disk)->delete($file->file_path);
        $file->delete();
    }

    private function moveFolder(Project $project, ProjectDrawingFolder $folder, string $targetFolder): void
    {
        $oldPath = $folder->path;
        $newPath = $this->uniqueFolderPath($project, trim($targetFolder.'/'.$folder->name, '/'), $folder->id);
        $project->drawingFolders()
            ->where(fn ($query) => $query->where('path', $oldPath)->orWhere('path', 'like', $oldPath.'/%'))
            ->get()
            ->each(function (ProjectDrawingFolder $childFolder) use ($newPath, $oldPath, $targetFolder) {
                $relativePath = trim(substr($childFolder->path, strlen($oldPath)), '/');
                $updatedPath = trim($newPath.'/'.$relativePath, '/');
                $childFolder->update([
                    'path' => $updatedPath,
                    'parent_path' => $updatedPath === $newPath ? $targetFolder : dirname($updatedPath),
                ]);
            });

        $project->drawings()
            ->where(fn ($query) => $query->where('folder_path', $oldPath)->orWhere('folder_path', 'like', $oldPath.'/%'))
            ->get()
            ->each(function (ProjectDrawing $file) use ($newPath, $oldPath) {
                $relativePath = trim(substr($file->folder_path, strlen($oldPath)), '/');
                $file->update(['folder_path' => trim($newPath.'/'.$relativePath, '/')]);
            });
    }

    private function copyFolder(Project $project, ProjectDrawingFolder $folder, string $targetFolder, int $createdBy): void
    {
        $oldPath = $folder->path;
        $newPath = $this->uniqueFolderPath($project, trim($targetFolder.'/'.$folder->name, '/'));

        $project->drawingFolders()
            ->where(fn ($query) => $query->where('path', $oldPath)->orWhere('path', 'like', $oldPath.'/%'))
            ->get()
            ->each(function (ProjectDrawingFolder $childFolder) use ($createdBy, $newPath, $oldPath, $project, $targetFolder) {
                $relativePath = trim(substr($childFolder->path, strlen($oldPath)), '/');
                $updatedPath = trim($newPath.'/'.$relativePath, '/');
                ProjectDrawingFolder::create([
                    'project_id' => $project->id,
                    'created_by' => $createdBy,
                    'name' => $childFolder->name,
                    'path' => $updatedPath,
                    'parent_path' => $updatedPath === $newPath ? $targetFolder : dirname($updatedPath),
                ]);
            });

        $project->drawings()
            ->where(fn ($query) => $query->where('folder_path', $oldPath)->orWhere('folder_path', 'like', $oldPath.'/%'))
            ->get()
            ->each(function (ProjectDrawing $file) use ($createdBy, $newPath, $oldPath, $project) {
                $relativePath = trim(substr($file->folder_path, strlen($oldPath)), '/');
                $this->copyFile($project, $file, trim($newPath.'/'.$relativePath, '/'), $createdBy);
            });
    }

    private function copyFile(Project $project, ProjectDrawing $file, string $targetFolder, int $uploadedBy): void
    {
        $storedName = Str::uuid().($file->extension ? '.'.$file->extension : '');
        $path = "projects/{$project->company_id}/{$project->id}/drawings/copied/{$storedName}";
        Storage::disk($file->disk)->copy($file->file_path, $path);
        $project->drawings()->create([
            'uploaded_by' => $uploadedBy,
            'original_name' => $file->original_name,
            'stored_name' => $storedName,
            'file_path' => $path,
            'folder_path' => $targetFolder,
            'disk' => $file->disk,
            'extension' => $file->extension,
            'mime_type' => $file->mime_type,
            'file_size' => $file->file_size,
            'extracted_from_id' => $file->extracted_from_id,
            'uploaded_at' => now(),
        ]);
    }

    private function uniqueFolderPath(Project $project, string $path, ?int $ignoreFolderId = null): string
    {
        $path = $this->normalizeFolderPath($path);
        $basePath = $path;
        $counter = 2;

        while ($project->drawingFolders()
            ->where('path', $path)
            ->when($ignoreFolderId, fn ($query) => $query->whereKeyNot($ignoreFolderId))
            ->exists()) {
            $path = $basePath.' Copy'.($counter > 2 ? ' '.$counter : '');
            $counter++;
        }

        return $path;
    }

    private function redirectToProjectFolder(Request $request, Project $project, string $folder)
    {
        $route = $request->user()->isStaff() ? 'staff.projects.show' : 'customer.projects.show';

        return redirect()->route($route, ['project' => $project, 'folder' => $folder]);
    }

    private function drawingUploadMessages(): array
    {
        return [
            'drawings.required' => 'Choose at least one drawing file to upload.',
            'drawings.*.uploaded' => 'The :attribute could not be uploaded. Maximum file size is '.$this->drawingUploadLimitLabel().' per file. If the file is smaller, check that the server temporary upload folder is writable.',
            'drawings.*.max' => 'The :attribute must be '.$this->appUploadLimitLabel().' or smaller.',
        ];
    }

    private function drawingUploadAttributes(): array
    {
        return [
            'drawings.*' => 'drawing file',
        ];
    }

    private function drawingUploadLimitLabel(): string
    {
        return $this->formatBytes(min(
            (float) SymfonyUploadedFile::getMaxFilesize(),
            (float) config('projects.files.max_kb', 20480) * 1024
        ));
    }

    private function appUploadLimitLabel(): string
    {
        return $this->formatBytes((float) config('projects.files.max_kb', 20480) * 1024);
    }

    private function formatBytes(float $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return rtrim(rtrim(number_format($bytes / 1024 / 1024, 1), '0'), '.').' MB';
        }

        return rtrim(rtrim(number_format($bytes / 1024, 1), '0'), '.').' KB';
    }
}
