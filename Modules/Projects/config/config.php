<?php

return [
    'name' => 'Projects',
    'files' => [
        'disk' => env('PORTAL_FILESYSTEM_DISK', 'local'),
        'max_kb' => (int) env('PORTAL_FILE_MAX_KB', 20480),
        'drawing_extensions' => ['pdf', 'xls', 'xlsx', 'csv'],
        'deliverable_extensions' => ['pdf', 'xls', 'xlsx', 'csv', 'ifc', 'zip'],
    ],
];
