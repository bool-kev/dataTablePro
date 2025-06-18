<?php

use App\Models\ImportedData;
use App\Services\ExportService;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

describe('ExportService', function () {
    beforeEach(function () {
        Storage::fake('public');
        
        ['user' => $this->user, 'workspace' => $this->workspace] = createUserWithWorkspace();
        actingAsUserInWorkspace($this->user, $this->workspace);
        
        $this->exportService = app(ExportService::class);
        $this->importHistory = createImportHistory($this->workspace);
        
        // Créer des données test
        $this->testData = [
            createImportedData($this->importHistory, ['name' => 'John Doe', 'email' => 'john@test.com', 'age' => 30]),
            createImportedData($this->importHistory, ['name' => 'Jane Smith', 'email' => 'jane@test.com', 'age' => 25]),
            createImportedData($this->importHistory, ['name' => 'Bob Johnson', 'email' => 'bob@test.com', 'age' => 35]),
        ];
    });

    describe('CSV Export', function () {
        it('can export data to CSV format', function () {
            $filename = $this->exportService->exportToCSV($this->workspace);
            
            expect($filename)->toBeString()
                ->and(Storage::disk('public')->exists("exports/{$filename}"))->toBeTrue();
            
            $content = Storage::disk('public')->get("exports/{$filename}");
            expect($content)->toContain('name,email,age')
                ->and($content)->toContain('John Doe')
                ->and($content)->toContain('Jane Smith')
                ->and($content)->toContain('Bob Johnson');
        });

        it('can export with custom column selection', function () {
            $filename = $this->exportService->exportToCSV($this->workspace, ['name', 'email']);
            
            $content = Storage::disk('public')->get("exports/{$filename}");
            expect($content)->toContain('name,email')
                ->and($content)->not->toContain('age')
                ->and($content)->toContain('John Doe,john@test.com');
        });

        it('can export filtered data', function () {
            $filters = ['age' => 30];
            $filename = $this->exportService->exportToCSV($this->workspace, null, $filters);
            
            $content = Storage::disk('public')->get("exports/{$filename}");
            expect($content)->toContain('John Doe')
                ->and($content)->not->toContain('Jane Smith')
                ->and($content)->not->toContain('Bob Johnson');
        });

        it('handles special characters in CSV export', function () {
            createImportedData($this->importHistory, [
                'name' => 'José María',
                'description' => 'A person with "quotes" and, commas',
                'notes' => 'Line 1\nLine 2'
            ]);
            
            $filename = $this->exportService->exportToCSV($this->workspace);
            $content = Storage::disk('public')->get("exports/{$filename}");
            
            expect($content)->toContain('José María')
                ->and($content)->toContain('"A person with ""quotes"" and, commas"');
        });

        it('generates unique filenames for each export', function () {
            $filename1 = $this->exportService->exportToCSV($this->workspace);
            $filename2 = $this->exportService->exportToCSV($this->workspace);
            
            expect($filename1)->not->toBe($filename2);
        });

        it('handles empty data gracefully', function () {
            ImportedData::query()->delete();
            
            $filename = $this->exportService->exportToCSV($this->workspace);
            $content = Storage::disk('public')->get("exports/{$filename}");
            
            expect($content)->toBe('');
        });
    });

    describe('Excel Export', function () {
        it('can export data to Excel format', function () {
            // Mock Excel facade pour les tests
            Excel::fake();
            
            $filename = $this->exportService->exportToExcel($this->workspace);
            
            expect($filename)->toEndWith('.xlsx')
                ->and(Storage::disk('public')->exists("exports/{$filename}"))->toBeTrue();
            
            Excel::assertDownloaded($filename);
        });

        it('can export with custom worksheet name', function () {
            Excel::fake();
            
            $filename = $this->exportService->exportToExcel($this->workspace, null, null, 'Custom Data');
            
            Excel::assertDownloaded($filename, function ($export) {
                return $export->title() === 'Custom Data';
            });
        });

        it('can export multiple sheets', function () {
            Excel::fake();
            
            // Créer des données avec différentes catégories
            createImportedData($this->importHistory, ['category' => 'customers', 'name' => 'Customer 1']);
            createImportedData($this->importHistory, ['category' => 'suppliers', 'name' => 'Supplier 1']);
            
            $filename = $this->exportService->exportToExcelWithSheets($this->workspace, 'category');
            
            Excel::assertDownloaded($filename);
        });
    });

    describe('Export with Search and Filters', function () {
        beforeEach(function () {
            // Créer plus de données variées pour les tests
            createImportedData($this->importHistory, ['name' => 'Alice Cooper', 'department' => 'IT', 'salary' => 50000]);
            createImportedData($this->importHistory, ['name' => 'Charlie Brown', 'department' => 'HR', 'salary' => 45000]);
            createImportedData($this->importHistory, ['name' => 'Diana Prince', 'department' => 'IT', 'salary' => 60000]);
        });

        it('can export search results', function () {
            $searchTerm = 'Alice';
            $filename = $this->exportService->exportSearchResults($this->workspace, $searchTerm);
            
            $content = Storage::disk('public')->get("exports/{$filename}");
            expect($content)->toContain('Alice Cooper')
                ->and($content)->not->toContain('Charlie Brown');
        });

        it('can export with multiple filters', function () {
            $filters = ['department' => 'IT'];
            $filename = $this->exportService->exportToCSV($this->workspace, null, $filters);
            
            $content = Storage::disk('public')->get("exports/{$filename}");
            expect($content)->toContain('Alice Cooper')
                ->and($content)->toContain('Diana Prince')
                ->and($content)->not->toContain('Charlie Brown');
        });

        it('can export with sorting applied', function () {
            $filename = $this->exportService->exportToCSV($this->workspace, null, null, 'salary', 'desc');
            
            $content = Storage::disk('public')->get("exports/{$filename}");
            $lines = explode("\n", trim($content));
            
            // La première ligne après l'en-tête devrait être Diana Prince (salaire le plus élevé)
            expect($lines[1])->toContain('Diana Prince');
        });
    });

    describe('Performance and Large Datasets', function () {
        it('can handle large datasets efficiently', function () {
            // Créer beaucoup de données
            for ($i = 1; $i <= 1000; $i++) {
                createImportedData($this->importHistory, [
                    'name' => "User $i",
                    'email' => "user$i@test.com",
                    'index' => $i
                ]);
            }
            
            $startTime = microtime(true);
            $filename = $this->exportService->exportToCSV($this->workspace);
            $endTime = microtime(true);
            
            expect($endTime - $startTime)->toBeLessThan(5.0) // Moins de 5 secondes
                ->and(Storage::disk('public')->exists("exports/{$filename}"))->toBeTrue();
            
            $content = Storage::disk('public')->get("exports/{$filename}");
            expect(substr_count($content, "\n"))->toBeGreaterThan(1000); // Plus de 1000 lignes
        });

        it('uses memory-efficient streaming for large exports', function () {
            // Créer beaucoup de données
            for ($i = 1; $i <= 5000; $i++) {
                createImportedData($this->importHistory, ['name' => "User $i", 'data' => str_repeat('x', 100)]);
            }
            
            $initialMemory = memory_get_usage();
            $filename = $this->exportService->exportToCSVStream($this->workspace);
            $finalMemory = memory_get_usage();
            
            $memoryIncrease = $finalMemory - $initialMemory;
            expect($memoryIncrease)->toBeLessThan(50 * 1024 * 1024); // Moins de 50MB d'augmentation
        });

        it('can process exports in chunks', function () {
            // Créer beaucoup de données
            for ($i = 1; $i <= 2000; $i++) {
                createImportedData($this->importHistory, ['name' => "User $i"]);
            }
            
            $filename = $this->exportService->exportToCSVInChunks($this->workspace, 500);
            
            expect(Storage::disk('public')->exists("exports/{$filename}"))->toBeTrue();
            
            $content = Storage::disk('public')->get("exports/{$filename}");
            expect(substr_count($content, "\n"))->toBeGreaterThan(2000);
        });
    });

    describe('Data Formatting and Transformation', function () {
        it('can apply custom formatters to exported data', function () {
            createImportedData($this->importHistory, [
                'name' => 'john doe',
                'email' => 'JOHN@TEST.COM',
                'created_at' => '2023-01-15 10:30:00'
            ]);
            
            $formatters = [
                'name' => 'title_case',
                'email' => 'lowercase',
                'created_at' => 'date_format:Y-m-d'
            ];
            
            $filename = $this->exportService->exportWithFormatters($this->workspace, $formatters);
            $content = Storage::disk('public')->get("exports/{$filename}");
            
            expect($content)->toContain('John Doe')
                ->and($content)->toContain('john@test.com')
                ->and($content)->toContain('2023-01-15');
        });

        it('can exclude sensitive data from exports', function () {
            createImportedData($this->importHistory, [
                'name' => 'John Doe',
                'email' => 'john@test.com',
                'password' => 'secret123',
                'ssn' => '123-45-6789'
            ]);
            
            $excludeColumns = ['password', 'ssn'];
            $filename = $this->exportService->exportWithExclusions($this->workspace, $excludeColumns);
            $content = Storage::disk('public')->get("exports/{$filename}");
            
            expect($content)->toContain('John Doe')
                ->and($content)->not->toContain('secret123')
                ->and($content)->not->toContain('123-45-6789');
        });

        it('can transform nested JSON data for export', function () {
            createImportedData($this->importHistory, [
                'name' => 'John Doe',
                'address' => [
                    'street' => '123 Main St',
                    'city' => 'Paris',
                    'country' => 'France'
                ],
                'tags' => ['customer', 'vip']
            ]);
            
            $filename = $this->exportService->exportWithJsonFlattening($this->workspace);
            $content = Storage::disk('public')->get("exports/{$filename}");
            
            expect($content)->toContain('address.street')
                ->and($content)->toContain('123 Main St')
                ->and($content)->toContain('customer,vip');
        });
    });

    describe('Export Templates and Formats', function () {
        it('can use predefined export templates', function () {
            $template = [
                'columns' => ['name', 'email'],
                'headers' => ['Full Name', 'Email Address'],
                'format' => 'csv'
            ];
            
            $filename = $this->exportService->exportWithTemplate($this->workspace, $template);
            $content = Storage::disk('public')->get("exports/{$filename}");
            
            expect($content)->toContain('Full Name,Email Address')
                ->and($content)->toContain('John Doe,john@test.com');
        });

        it('can export with custom headers', function () {
            $columnMapping = [
                'name' => 'Customer Name',
                'email' => 'Contact Email',
                'age' => 'Customer Age'
            ];
            
            $filename = $this->exportService->exportWithCustomHeaders($this->workspace, $columnMapping);
            $content = Storage::disk('public')->get("exports/{$filename}");
            
            expect($content)->toContain('Customer Name,Contact Email,Customer Age');
        });
    });

    describe('Error Handling and Validation', function () {
        it('handles workspace with no data gracefully', function () {
            ImportedData::query()->delete();
            
            $filename = $this->exportService->exportToCSV($this->workspace);
            
            expect($filename)->toBeString()
                ->and(Storage::disk('public')->exists("exports/{$filename}"))->toBeTrue();
        });

        it('validates column selections', function () {
            expect(fn() => $this->exportService->exportToCSV($this->workspace, ['non_existent_column']))
                ->not->toThrow();
        });

        it('handles storage errors gracefully', function () {
            // Simuler une erreur de stockage
            Storage::shouldReceive('disk')->andThrow(new \Exception('Storage error'));
            
            expect(fn() => $this->exportService->exportToCSV($this->workspace))
                ->toThrow(\Exception::class, 'Storage error');
        });

        it('cleans up temporary files on failure', function () {
            // Simuler une erreur pendant l'export
            $this->mock(\App\Repositories\ImportedDataRepository::class)
                ->shouldReceive('getForExport')
                ->andThrow(new \Exception('Database error'));
            
            expect(fn() => $this->exportService->exportToCSV($this->workspace))
                ->toThrow(\Exception::class);
            
            // Vérifier que les fichiers temporaires ont été nettoyés
            $tempFiles = Storage::disk('public')->files('temp');
            expect($tempFiles)->toHaveCount(0);
        });
    });

    describe('Export Metadata and Tracking', function () {
        it('generates export metadata', function () {
            $result = $this->exportService->exportWithMetadata($this->workspace);
            
            expect($result)->toHaveKey('filename')
                ->and($result)->toHaveKey('row_count')
                ->and($result)->toHaveKey('file_size')
                ->and($result)->toHaveKey('export_time')
                ->and($result['row_count'])->toBe(3);
        });

        it('tracks export history', function () {
            $this->exportService->exportToCSV($this->workspace);
            
            $exports = $this->exportService->getExportHistory($this->workspace);
            
            expect($exports)->toHaveCount(1)
                ->and($exports->first())->toHaveKey('filename')
                ->and($exports->first())->toHaveKey('created_at');
        });

        it('can limit export file retention', function () {
            // Créer plusieurs exports
            for ($i = 1; $i <= 10; $i++) {
                $this->exportService->exportToCSV($this->workspace);
            }
            
            $this->exportService->cleanupOldExports($this->workspace, 5);
            
            $remainingFiles = Storage::disk('public')->files('exports');
            expect($remainingFiles)->toHaveCount(5);
        });
    });
});
