<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\ImportHistory;
use App\Models\ImportedData;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeValidJson', function () {
    $decoded = json_decode($this->value, true);
    return expect($decoded)->not->toBeNull()->and(json_last_error())->toBe(JSON_ERROR_NONE);
});

expect()->extend('toHaveWorkspaceAccess', function ($workspace) {
    return $this->toBeTrue();
});

expect()->extend('toBeInWorkspace', function ($workspace) {
    return expect($this->value->workspace_id ?? $this->value->importHistory->workspace_id)
        ->toBe($workspace->id);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function createUserWithWorkspace(array $userAttributes = [], array $workspaceAttributes = []): array
{
    $user = User::factory()->create($userAttributes);
    $workspace = Workspace::factory()->create(array_merge(['owner_id' => $user->id], $workspaceAttributes));
    $workspace->users()->attach($user->id, ['role' => 'owner']);
    
    return compact('user', 'workspace');
}

function createImportHistory(Workspace $workspace, array $attributes = []): ImportHistory
{
    return ImportHistory::create(array_merge([
        'workspace_id' => $workspace->id,
        'filename' => 'test.csv',
        'original_filename' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'file_type' => 'csv',
        'status' => 'completed',
        'total_rows' => 2,
        'successful_rows' => 2,
        'failed_rows' => 0,
    ], $attributes));
}

function createImportedData(ImportHistory $importHistory, array $data = [], array $attributes = []): ImportedData
{
    $defaultData = [
        'name' => 'John Doe', 
        'email' => 'john@test.com', 
        'age' => 30,
        'unique_id' => \Illuminate\Support\Str::uuid() // Ajouter un ID unique
    ];
    $finalData = array_merge($defaultData, $data);
    
    return ImportedData::create(array_merge([
        'import_history_id' => $importHistory->id,
        'data' => $finalData,
        'row_hash' => md5(json_encode($finalData) . microtime() . mt_rand())
    ], $attributes));
}

function createCsvFile(string $filename = 'test.csv', array $rows = null): UploadedFile
{
    $defaultRows = [
        ['name', 'email', 'age'],
        ['John Doe', 'john@example.com', '30'],
        ['Jane Smith', 'jane@example.com', '25']
    ];
    
    $rows = $rows ?? $defaultRows;
    $content = implode("\n", array_map(fn($row) => implode(',', $row), $rows));
    
    return UploadedFile::fake()->createWithContent($filename, $content);
}

function createExcelFile(string $filename = 'test.xlsx'): UploadedFile
{
    return UploadedFile::fake()->create($filename, 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
}

function actingAsUserInWorkspace(User $user, Workspace $workspace): void
{
    test()->actingAs($user);
    session(['current_workspace_id' => $workspace->id]);
}
