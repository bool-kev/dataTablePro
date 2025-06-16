<?php

use App\Models\ImportHistory;
use App\Services\ImportService;
use App\Repositories\ImportedDataRepository;
use App\Repositories\ImportHistoryRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->importedDataRepo = app(ImportedDataRepository::class);
    $this->importHistoryRepo = app(ImportHistoryRepository::class);
    $this->importService = app(ImportService::class);
});

it('can process a CSV file', function () {
    // Créer un fichier CSV test
    $csvContent = "name,email,age\nJohn Doe,john@example.com,30\nJane Smith,jane@example.com,25";
    $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);
    
    // Traiter le fichier
    $importHistory = $this->importService->processFile($file);
    
    expect($importHistory)->toBeInstanceOf(ImportHistory::class);
    expect($importHistory->status)->toBe('completed');
    expect($importHistory->successful_rows)->toBe(2);
    expect($importHistory->failed_rows)->toBe(0);
    expect($importHistory->total_rows)->toBe(2);
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
    $this->importService->processFile($file);
    
    // Deuxième import (devrait détecter les doublons)
    $importHistory2 = $this->importService->processFile($file);
    
    expect($importHistory2->failed_rows)->toBeGreaterThan(0);
});

it('can get unique columns from imported data', function () {
    // Insérer des données test
    $this->importedDataRepo->create([
        'import_history_id' => 1,
        'data' => ['name' => 'John', 'email' => 'john@test.com', 'age' => 30],
        'row_hash' => 'test1'
    ]);
    
    $this->importedDataRepo->create([
        'import_history_id' => 1,
        'data' => ['name' => 'Jane', 'phone' => '123456789'],
        'row_hash' => 'test2'
    ]);
    
    $columns = $this->importService->getUniqueColumns();
    
    expect($columns)->toContain('name', 'email', 'age', 'phone');
});
