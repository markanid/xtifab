<?php

namespace Modules\Projects\Contracts;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface ProjectFileStorageInterface
{
    public function upload(UploadedFile $file, string $directory): array;

    public function download(string $disk, string $path, string $name): StreamedResponse;

    public function delete(string $disk, string $path): bool;

    public function exists(string $disk, string $path): bool;
}
