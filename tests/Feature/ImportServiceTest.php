<?php

use App\Models\ImportHistory;
use App\Models\User;
use App\Models\Workspace;
use App\Services\ImportService;
use App\Repositories\ImportedDataRepository;
use App\Repositories\ImportHistoryRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    
    // Créer un utilisateur et un workspace
    $this->user = User::factory()->create();
    $this->workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
    
    // Authentifier l'utilisateur et définir le workspace courant
    $this->actingAs($this->user);
    session(['current_workspace_id' => $this->workspace->id]);
    
    $this->importedDataRepo = app(ImportedDataRepository::class);
    $this->importHistoryRepo = app(ImportHistoryRepository::class);
    $this->importService = app(ImportService::class);
});

it('can process a CSV file', function () {
    // Créer un fichier CSV test
    $csvContent = "name,email,age\nJohn Doe,john@example.com,30\nJane Smith,jane@example.com,25";
    $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);
    
    // Traiter le fichier
    $importHistory = $this->importService->processFile($file, $this->workspace);
    
    expect($importHistory)->toBeInstanceOf(ImportHistory::class);
    expect($importHistory->status)->toBe('completed');
    expect($importHistory->successful_rows)->toBe(2);
    expect($importHistory->failed_rows)->toBe(0);
    expect($importHistory->total_rows)->toBe(2);
    expect($importHistory->workspace_id)->toBe($this->workspace->id);
});

it('can handle invalid CSV files gracefully', function () {
    // Créer un fichier avec un contenu invalide
    $file = UploadedFile::fake()->createWithContent('invalid.txt', 'invalid content');
    
    expect(fn() => $this->importService->processFile($file))
        ->toThrow(InvalidArgumentException::class);
});

it('can detect duplicate rows', function () {
    // Créer un fichier CSV avec des doublons
    $csvContent = "name,email\nJohn Doe,john@example.com\nJohn Doe,john@example.com";
    $file = UploadedFile::fake()->createWithContent('duplicates.csv', $csvContent);
    
    // Premier import
    $this->importService->processFile($file, $this->workspace);
    
    // Deuxième import (devrait détecter les doublons)
    $importHistory2 = $this->importService->processFile($file, $this->workspace);
    
    expect($importHistory2->failed_rows)->toBeGreaterThan(0);
});

it('can get unique columns from imported data', function () {
    // Créer un historique d'import pour le workspace
    $importHistory = \App\Models\ImportHistory::create([
        'workspace_id' => $this->workspace->id,
        'filename' => 'test.csv',
        'original_filename' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'file_type' => 'csv',
        'status' => 'completed',
        'total_rows' => 2,
        'successful_rows' => 2,
        'failed_rows' => 0,
    ]);
    
    // Insérer des données test
    $this->importedDataRepo->create([
        'import_history_id' => $importHistory->id,
        'data' => ['name' => 'John', 'email' => 'john@test.com', 'age' => 30],
        'row_hash' => 'test1'
    ]);
    
    $this->importedDataRepo->create([
        'import_history_id' => $importHistory->id,
        'data' => ['name' => 'Jane', 'phone' => '123456789'],
        'row_hash' => 'test2'
    ]);
    
    $columns = $this->importedDataRepo->getUniqueColumns($this->workspace);
    
    expect($columns)->toContain('name', 'email', 'age', 'phone');
});
