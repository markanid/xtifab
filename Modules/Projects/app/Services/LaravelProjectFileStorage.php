<?php

namespace Modules\Projects\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Projects\Contracts\ProjectFileStorageInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaravelProjectFileStorage implements ProjectFileStorageInterface
{
    public function upload(UploadedFile $file, string $directory): array
    {
        $disk = config('projects.files.disk', env('PORTAL_FILESYSTEM_DISK', 'local'));
        $storedName = Str::uuid().'.'.strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs($directory, $storedName, $disk);

        return ['disk' => $disk, 'file_path' => $path, 'stored_name' => $storedName,
            'original_name' => $file->getClientOriginalName(), 'extension' => strtolower($file->getClientOriginalExtension()),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream', 'file_size' => $file->getSize()];
    }

    public function download(string $disk, string $path, string $name): StreamedResponse
    {
        return Storage::disk($disk)->download($path, $name);
    }

    public function delete(string $disk, string $path): bool
    {
        return Storage::disk($disk)->delete($path);
    }

    public function exists(string $disk, string $path): bool
    {
        return Storage::disk($disk)->exists($path);
    }
}
