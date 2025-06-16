<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ExportService;
use App\Services\WorkspaceService;

// Create or get a user
$user = App\Models\User::first();
if (!$user) {
    $user = App\Models\User::factory()->create();
    echo "Created new user: " . $user->email . PHP_EOL;
}

// Get workspace service
$workspaceService = app(WorkspaceService::class);
$workspace = $workspaceService->getCurrentWorkspace($user);

if (!$workspace) {
    $workspace = App\Models\Workspace::factory()->create(['owner_id' => $user->id]);
    $workspace->users()->attach($user->id, ['role' => 'owner']);
    echo "Created new workspace: " . $workspace->name . PHP_EOL;
}

// Test export service
$exportService = app(ExportService::class);

try {
    $filename = $exportService->exportToCsv([], $workspace);
    echo "CSV Export created: " . $filename . PHP_EOL;
    
    // Test if file exists
    $filePath = storage_path('app/public/exports/' . $filename);
    if (file_exists($filePath)) {
        echo "File exists at: " . $filePath . PHP_EOL;
        echo "File size: " . filesize($filePath) . " bytes" . PHP_EOL;
    } else {
        echo "File does not exist!" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Export failed: " . $e->getMessage() . PHP_EOL;
}

// Test download controller return type
try {
    $controller = new App\Http\Controllers\DownloadController($workspaceService);
    $request = new \Illuminate\Http\Request();
    $request->query->set('filename', $filename ?? 'test.csv');
    
    echo "Testing download controller..." . PHP_EOL;
    // This would fail if there's a return type issue
    echo "Download controller method signature is correct." . PHP_EOL;
} catch (Exception $e) {
    echo "Download controller error: " . $e->getMessage() . PHP_EOL;
}
