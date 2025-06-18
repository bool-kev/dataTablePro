<?php

use App\Models\ImportHistory;
use App\Models\ImportedData;
use App\Models\User;
use App\Models\Workspace;
use App\Services\ImportService;
use App\Repositories\ImportedDataRepository;
use App\Repositories\ImportHistoryRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    
    // Utiliser la fonction helper du Pest.php
    ['user' => $this->user, 'workspace' => $this->workspace] = createUserWithWorkspace();
    
    // Authentifier l'utilisateur et définir le workspace courant
    actingAsUserInWorkspace($this->user, $this->workspace);
    
    $this->importedDataRepo = app(ImportedDataRepository::class);
    $this->importHistoryRepo = app(ImportHistoryRepository::class);
    $this->importService = app(ImportService::class);
});

describe('CSV File Processing', function () {
    it('can process a valid CSV file successfully', function () {
        $file = createCsvFile('test.csv');
        
        $importHistory = $this->importService->processFile($file, $this->workspace);
        
        expect($importHistory)->toBeInstanceOf(ImportHistory::class)
            ->and($importHistory->status)->toBe('completed')
            ->and($importHistory->successful_rows)->toBe(2)
            ->and($importHistory->failed_rows)->toBe(0)
            ->and($importHistory->total_rows)->toBe(2)
            ->and($importHistory->workspace_id)->toBe($this->workspace->id)
            ->and($importHistory->file_type)->toBe('csv');
        
        // Vérifier que les données ont été stockées
        $importedData = ImportedData::where('import_history_id', $importHistory->id)->get();
        expect($importedData)->toHaveCount(2);
        
        expect($importedData->first()->data)
            ->toHaveKey('name')
            ->toHaveKey('email')
            ->toHaveKey('age');
    });

    it('can handle CSV files with different encodings', function () {
        $csvContent = "nom,email,âge\nJéan Dûpont,jean@example.com,30\nMárié Smîth,marie@example.com,25";
        $file = UploadedFile::fake()->createWithContent('test_utf8.csv', $csvContent);
        
        $importHistory = $this->importService->processFile($file, $this->workspace);
        
        expect($importHistory->status)->toBe('completed')
            ->and($importHistory->successful_rows)->toBe(2);
    });

    it('can handle CSV files with custom delimiters', function () {
        $csvContent = "name;email;age\nJohn Doe;john@example.com;30\nJane Smith;jane@example.com;25";
        $file = UploadedFile::fake()->createWithContent('test_semicolon.csv', $csvContent);
        
        $importHistory = $this->importService->processFile($file, $this->workspace);
        
        expect($importHistory->status)->toBe('completed')
            ->and($importHistory->successful_rows)->toBe(2);
    });

    it('can handle CSV files with quoted fields', function () {
        $csvContent = '"name","email","description"\n"John Doe","john@example.com","A person with, commas"\n"Jane Smith","jane@example.com","Another ""quoted"" description"';
        $file = UploadedFile::fake()->createWithContent('test_quoted.csv', $csvContent);
        
        $importHistory = $this->importService->processFile($file, $this->workspace);
        
        expect($importHistory->status)->toBe('completed')
            ->and($importHistory->successful_rows)->toBe(2);
        
        $data = ImportedData::where('import_history_id', $importHistory->id)->first();
        expect($data->data['description'])->toContain('commas');
    });

    it('can handle large CSV files', function () {
        // Créer un gros fichier CSV
        $rows = [['name', 'email', 'age']];
        for ($i = 1; $i <= 1000; $i++) {
            $rows[] = ["User $i", "user$i@example.com", rand(18, 80)];
        }
        
        $file = createCsvFile('large.csv', $rows);
        
        $importHistory = $this->importService->processFile($file, $this->workspace);
        
        expect($importHistory->status)->toBe('completed')
            ->and($importHistory->successful_rows)->toBe(1000)
            ->and($importHistory->total_rows)->toBe(1000);
    });
});

describe('Excel File Processing', function () {
    it('can process Excel files', function () {
        // Simuler un fichier Excel valide
        $file = createExcelFile('test.xlsx');
        
        // Mock du service Excel pour les tests
        $this->mock(\Maatwebsite\Excel\Facades\Excel::class)
            ->shouldReceive('toArray')
            ->once()
            ->andReturn([
                [
                    ['name' => 'John Doe', 'email' => 'john@example.com', 'age' => 30],
                    ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'age' => 25]
                ]
            ]);
        
        expect(fn() => $this->importService->processFile($file, $this->workspace))
            ->not->toThrow();
    });
});

describe('Error Handling', function () {
    it('throws exception for invalid file types', function () {
        $file = UploadedFile::fake()->createWithContent('invalid.txt', 'invalid content');
        
        expect(fn() => $this->importService->processFile($file, $this->workspace))
            ->toThrow(InvalidArgumentException::class, 'Unsupported file type');
    });

    it('handles corrupted CSV files gracefully', function () {
        $corruptedContent = "name,email\nJohn,john@example.com\n\"unclosed quote,field";
        $file = UploadedFile::fake()->createWithContent('corrupted.csv', $corruptedContent);
        
        $importHistory = $this->importService->processFile($file, $this->workspace);
        
        expect($importHistory->status)->toBeIn(['completed', 'completed_with_errors'])
            ->and($importHistory->failed_rows)->toBeGreaterThan(0);
    });

    it('handles empty CSV files', function () {
        $file = UploadedFile::fake()->createWithContent('empty.csv', '');
        
        expect(fn() => $this->importService->processFile($file, $this->workspace))
            ->toThrow(InvalidArgumentException::class, 'File is empty');
    });

    it('handles CSV files with only headers', function () {
        $file = createCsvFile('headers_only.csv', [['name', 'email', 'age']]);
        
        $importHistory = $this->importService->processFile($file, $this->workspace);
        
        expect($importHistory->status)->toBe('completed')
            ->and($importHistory->total_rows)->toBe(0)
            ->and($importHistory->successful_rows)->toBe(0);
    });
});

describe('Duplicate Detection', function () {
    it('can detect and handle duplicate rows', function () {
        $csvContent = "name,email\nJohn Doe,john@example.com\nJohn Doe,john@example.com";
        $file = UploadedFile::fake()->createWithContent('duplicates.csv', $csvContent);
        
        // Premier import
        $firstImport = $this->importService->processFile($file, $this->workspace);
        expect($firstImport->successful_rows)->toBe(2);
        
        // Deuxième import (devrait détecter les doublons)
        $secondImport = $this->importService->processFile($file, $this->workspace);
        
        expect($secondImport->failed_rows)->toBe(2)
            ->and($secondImport->successful_rows)->toBe(0);
    });

    it('can handle duplicates within the same file', function () {
        $csvContent = "name,email\nJohn Doe,john@example.com\nJohn Doe,john@example.com\nJane Smith,jane@example.com";
        $file = UploadedFile::fake()->createWithContent('internal_duplicates.csv', $csvContent);
        
        $importHistory = $this->importService->processFile($file, $this->workspace);
        
        expect($importHistory->successful_rows)->toBe(2) // John (premier) + Jane
            ->and($importHistory->failed_rows)->toBe(1); // John (deuxième occurrence)
    });
});

describe('Data Repository Integration', function () {
    it('can get unique columns from imported data', function () {
        $importHistory = createImportHistory($this->workspace);
        
        // Créer des données avec différentes colonnes
        createImportedData($importHistory, ['name' => 'John', 'email' => 'john@test.com', 'age' => 30]);
        createImportedData($importHistory, ['name' => 'Jane', 'phone' => '123456789', 'city' => 'Paris']);
        
        $columns = $this->importedDataRepo->getUniqueColumns($this->workspace);
        
        expect($columns)->toContain('name', 'email', 'age', 'phone', 'city')
            ->and($columns)->toHaveCount(5);
    });

    it('respects workspace isolation for columns', function () {
        // Créer un autre workspace
        ['workspace' => $otherWorkspace] = createUserWithWorkspace();
        
        $importHistory1 = createImportHistory($this->workspace);
        $importHistory2 = createImportHistory($otherWorkspace);
        
        createImportedData($importHistory1, ['column_a' => 'value1']);
        createImportedData($importHistory2, ['column_b' => 'value2']);
        
        $columns = $this->importedDataRepo->getUniqueColumns($this->workspace);
        
        expect($columns)->toContain('column_a')
            ->and($columns)->not->toContain('column_b');
    });
});

describe('Performance and Batch Processing', function () {
    it('can handle batch processing efficiently', function () {
        // Tester le traitement par lots
        $rows = [['name', 'email', 'batch_id']];
        for ($i = 1; $i <= 500; $i++) {
            $rows[] = ["User $i", "user$i@example.com", "batch_1"];
        }
        
        $file = createCsvFile('batch_test.csv', $rows);
        
        $startTime = microtime(true);
        $importHistory = $this->importService->processFile($file, $this->workspace);
        $endTime = microtime(true);
        
        $executionTime = $endTime - $startTime;
        
        expect($importHistory->status)->toBe('completed')
            ->and($importHistory->successful_rows)->toBe(500)
            ->and($executionTime)->toBeLessThan(10); // Moins de 10 secondes
    });
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
