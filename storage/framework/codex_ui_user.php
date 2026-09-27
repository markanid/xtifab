<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (($argv[1] ?? null) === 'delete') {
    User::where('email', 'codex-ui-check@local.test')->delete();

    return;
}

User::updateOrCreate(
    ['email' => 'codex-ui-check@local.test'],
    [
        'name' => 'Codex UI Check',
        'password' => 'Temporary!2026',
        'role' => 'super_admin',
        'status' => 'active',
        'must_change_password' => false,
    ],
);
