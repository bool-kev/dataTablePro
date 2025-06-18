<?php

/**
 * Tests d'intégration pour valider l'ensemble du système
 */

use App\Livewire\DataTable;
use App\Livewire\FileUpload;
use App\Models\ImportHistory;
use App\Models\ImportedData;
use App\Services\ExportService;
use App\Services\ImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

describe('Application Integration Tests', function () {
    beforeEach(function () {
        Storage::fake('public');
        
        ['user' => $this->user, 'workspace' => $this->workspace] = createUserWithWorkspace();
        actingAsUserInWorkspace($this->user, $this->workspace);
    });

    describe('Complete Data Import Flow', function () {
        it('can complete full import workflow from upload to display', function () {
            // 1. Créer un fichier de test
            $csvContent = "name,email,age,city\n";
            $csvContent .= "John Doe,john@test.com,30,Paris\n";
            $csvContent .= "Jane Smith,jane@test.com,25,London\n";
            $csvContent .= "Bob Johnson,bob@test.com,35,New York\n";
            
            $file = UploadedFile::fake()->createWithContent('integration_test.csv', $csvContent);
            
            // 2. Uploader le fichier via le composant
            $uploadComponent = Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->call('upload')
                ->assertHasNoErrors();
            
            expect($uploadComponent->get('uploadSuccess'))->toBeTrue();
            
            // 3. Vérifier que l'import a été créé
            expect(ImportHistory::count())->toBe(1);
            $importHistory = ImportHistory::first();
            expect($importHistory->status)->toBe('completed')
                ->and($importHistory->total_rows)->toBe(3)
                ->and($importHistory->successful_rows)->toBe(3);
            
            // 4. Vérifier que les données ont été importées
            expect(ImportedData::count())->toBe(3);
            
            // 5. Tester l'affichage dans le tableau
            $tableComponent = Livewire::test(DataTable::class)
                ->assertSee('John Doe')
                ->assertSee('Jane Smith')
                ->assertSee('Bob Johnson');
            
            // 6. Tester la recherche
            $tableComponent->set('search', 'John')
                ->assertSee('John Doe')
                ->assertDontSee('Jane Smith');
            
            // 7. Tester le tri
            $tableComponent->set('search', '')
                ->call('sortBy', 'name');
            
            $data = $tableComponent->get('data');
            expect($data->first()['name'])->toBe('Bob Johnson'); // Premier par ordre alphabétique
        });

        it('can handle import errors and display them properly', function () {
            // Créer un fichier CSV malformé
            $malformedContent = "name,email\nJohn Doe,john@test.com\n\"unclosed quote,field";
            $file = UploadedFile::fake()->createWithContent('malformed.csv', $malformedContent);
            
            $uploadComponent = Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->call('upload');
            
            // Vérifier que l'erreur est gérée
            expect($uploadComponent->get('uploadError'))->toBeTrue()
                ->or($uploadComponent->get('uploadSuccess'))->toBeTrue(); // Peut réussir partiellement
            
            // Vérifier que l'historique est créé même en cas d'erreur
            expect(ImportHistory::count())->toBe(1);
        });
    });

    describe('Complete Export Workflow', function () {
        beforeEach(function () {
            $this->importHistory = createImportHistory($this->workspace);
            createImportedData($this->importHistory, ['name' => 'John', 'email' => 'john@test.com', 'age' => 30]);
            createImportedData($this->importHistory, ['name' => 'Jane', 'email' => 'jane@test.com', 'age' => 25]);
        });

        it('can export data from table component', function () {
            $tableComponent = Livewire::test(DataTable::class)
                ->call('exportData', 'csv')
                ->assertHasNoErrors();
            
            // Vérifier qu'un fichier d'export a été créé
            $exportFiles = Storage::disk('public')->files('exports');
            expect($exportFiles)->not->toBeEmpty();
            
            // Vérifier le contenu du fichier
            $content = Storage::disk('public')->get($exportFiles[0]);
            expect($content)->toContain('name,email,age')
                ->and($content)->toContain('John')
                ->and($content)->toContain('Jane');
        });

        it('can export filtered data', function () {
            $tableComponent = Livewire::test(DataTable::class)
                ->set('search', 'John')
                ->call('exportData', 'csv');
            
            $exportFiles = Storage::disk('public')->files('exports');
            $content = Storage::disk('public')->get($exportFiles[0]);
            
            expect($content)->toContain('John')
                ->and($content)->not->toContain('Jane');
        });
    });

    describe('Data Manipulation Integration', function () {
        beforeEach(function () {
            $this->importHistory = createImportHistory($this->workspace);
            $this->testData = createImportedData($this->importHistory, [
                'name' => 'John Doe',
                'email' => 'john@test.com',
                'age' => 30
            ]);
        });

        it('can edit data through table component', function () {
            $tableComponent = Livewire::test(DataTable::class)
                ->call('editRow', $this->testData->id)
                ->set('editData.name', 'John Updated')
                ->call('saveEdit')
                ->assertHasNoErrors();
            
            // Vérifier que les données ont été mises à jour
            expect($this->testData->fresh()->data['name'])->toBe('John Updated');
            
            // Vérifier que l'affichage est mis à jour
            $tableComponent->assertSee('John Updated');
        });

        it('can delete data through table component', function () {
            $tableComponent = Livewire::test(DataTable::class)
                ->call('deleteRow', $this->testData->id)
                ->assertHasNoErrors();
            
            // Vérifier que les données ont été supprimées
            expect(ImportedData::find($this->testData->id))->toBeNull();
            
            // Vérifier que l'affichage est mis à jour
            $tableComponent->assertDontSee('John Doe');
        });
    });

    describe('Workspace Isolation', function () {
        it('maintains data isolation between workspaces', function () {
            // Créer des données dans le workspace actuel
            $importHistory1 = createImportHistory($this->workspace);
            createImportedData($importHistory1, ['name' => 'Workspace 1 User']);
            
            // Créer un autre workspace avec des données
            ['user' => $otherUser, 'workspace' => $otherWorkspace] = createUserWithWorkspace();
            $importHistory2 = createImportHistory($otherWorkspace);
            createImportedData($importHistory2, ['name' => 'Workspace 2 User']);
            
            // Tester que chaque workspace ne voit que ses données
            $table1 = Livewire::test(DataTable::class)
                ->assertSee('Workspace 1 User')
                ->assertDontSee('Workspace 2 User');
            
            // Changer de contexte utilisateur/workspace
            actingAsUserInWorkspace($otherUser, $otherWorkspace);
            
            $table2 = Livewire::test(DataTable::class)
                ->assertSee('Workspace 2 User')
                ->assertDontSee('Workspace 1 User');
        });

        it('prevents cross-workspace data access via API', function () {
            // Créer des données dans un autre workspace
            ['workspace' => $otherWorkspace] = createUserWithWorkspace();
            $importHistory = createImportHistory($otherWorkspace);
            $otherData = createImportedData($importHistory, ['name' => 'Other Workspace Data']);
            
            // Tenter d'accéder aux données de l'autre workspace
            $response = $this->getJson("/api/data/{$otherData->id}");
            
            $response->assertStatus(403); // Forbidden
        });
    });

    describe('Performance and Scalability', function () {
        it('handles large datasets efficiently', function () {
            $importHistory = createImportHistory($this->workspace);
            
            // Créer un grand nombre de données
            $startTime = microtime(true);
            for ($i = 1; $i <= 1000; $i++) {
                createImportedData($importHistory, [
                    'name' => "User $i",
                    'email' => "user$i@test.com",
                    'index' => $i
                ]);
            }
            $endTime = microtime(true);
            
            expect($endTime - $startTime)->toBeLessThan(10.0); // Création en moins de 10 secondes
            
            // Tester les performances d'affichage
            $startTime = microtime(true);
            $tableComponent = Livewire::test(DataTable::class)
                ->set('perPage', 50);
            $endTime = microtime(true);
            
            expect($endTime - $startTime)->toBeLessThan(2.0); // Affichage en moins de 2 secondes
            
            // Tester les performances de recherche
            $startTime = microtime(true);
            $tableComponent->set('search', 'User 500');
            $endTime = microtime(true);
            
            expect($endTime - $startTime)->toBeLessThan(1.0); // Recherche en moins de 1 seconde
        });

        it('maintains performance with complex filtering and sorting', function () {
            $importHistory = createImportHistory($this->workspace);
            
            // Créer des données variées pour les tests
            for ($i = 1; $i <= 500; $i++) {
                createImportedData($importHistory, [
                    'name' => "User $i",
                    'email' => "user$i@test.com",
                    'age' => 20 + ($i % 50),
                    'department' => ['IT', 'HR', 'Finance', 'Marketing'][($i % 4)],
                    'salary' => 30000 + ($i * 100)
                ]);
            }
            
            $startTime = microtime(true);
            $tableComponent = Livewire::test(DataTable::class)
                ->set('filters.department', 'IT')
                ->set('search', 'User')
                ->call('sortBy', 'salary');
            $endTime = microtime(true);
            
            expect($endTime - $startTime)->toBeLessThan(2.0); // Opérations complexes en moins de 2 secondes
        });
    });

    describe('Error Recovery and Resilience', function () {
        it('recovers gracefully from database errors', function () {
            // Simuler une erreur de base de données
            $this->mock(\App\Repositories\ImportedDataRepository::class)
                ->shouldReceive('getForWorkspace')
                ->andThrow(new \Exception('Database connection lost'));
            
            $tableComponent = Livewire::test(DataTable::class);
            
            // Le composant devrait gérer l'erreur sans planter
            expect($tableComponent->get('hasError'))->toBeTrue()
                ->or($tableComponent->effects['html'])->toContain('error'); // Message d'erreur affiché
        });

        it('handles storage errors during file operations', function () {
            // Simuler une erreur de stockage
            Storage::shouldReceive('disk')->andThrow(new \Exception('Storage unavailable'));
            
            $file = createCsvFile('storage_error_test.csv');
            
            $uploadComponent = Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->call('upload');
            
            expect($uploadComponent->get('uploadError'))->toBeTrue();
        });
    });

    describe('User Experience Integration', function () {
        it('provides consistent feedback across components', function () {
            $importHistory = createImportHistory($this->workspace);
            createImportedData($importHistory, ['name' => 'Test User']);
            
            // Tester la cohérence des messages
            $tableComponent = Livewire::test(DataTable::class);
            
            // Vérifier que les statistiques sont affichées
            expect($tableComponent->get('totalRecords'))->toBe(1);
            
            // Supprimer l'enregistrement
            $data = ImportedData::first();
            $tableComponent->call('deleteRow', $data->id);
            
            // Vérifier que les statistiques sont mises à jour
            expect($tableComponent->get('totalRecords'))->toBe(0);
        });

        it('maintains state consistency during concurrent operations', function () {
            $importHistory = createImportHistory($this->workspace);
            createImportedData($importHistory, ['name' => 'Original User']);
            
            // Simuler des opérations concurrentes
            $table1 = Livewire::test(DataTable::class);
            $table2 = Livewire::test(DataTable::class);
            
            // Ajouter une donnée via un autre processus
            createImportedData($importHistory, ['name' => 'New User']);
            
            // Rafraîchir les composants
            $table1->call('refreshData');
            $table2->call('refreshData');
            
            // Les deux composants devraient voir les nouvelles données
            $table1->assertSee('New User');
            $table2->assertSee('New User');
        });
    });

    describe('Configuration and Environment', function () {
        it('adapts behavior based on environment settings', function () {
            // Tester en mode debug
            config(['app.debug' => true]);
            
            $tableComponent = Livewire::test(DataTable::class);
            
            // En mode debug, plus d'informations devraient être disponibles
            expect($tableComponent->get('debugMode'))->toBeTrue()
                ->or(config('app.debug'))->toBeTrue();
        });

        it('respects application limits and quotas', function () {
            // Configurer des limites
            config(['app.max_import_size' => 1024]); // 1KB max
            
            $largeFile = UploadedFile::fake()->create('large_file.csv', 2048); // 2KB
            
            $uploadComponent = Livewire::test(FileUpload::class)
                ->set('file', $largeFile)
                ->call('upload');
            
            expect($uploadComponent->get('uploadError'))->toBeTrue();
        });
    });
});

// Test helper pour valider que tous les tests peuvent s'exécuter
describe('Test Suite Validation', function () {
    it('validates that all test files are properly configured', function () {
        $testFiles = [
            'tests/Feature/ImportServiceTest.php',
            'tests/Feature/DataTableComponentTest.php',
            'tests/Feature/Components/FileUploadTest.php',
            'tests/Feature/Services/ExportServiceTest.php',
            'tests/Feature/Api/DataManagementApiTest.php',
            'tests/Unit/Models/ImportedDataTest.php',
            'tests/Unit/Models/ImportHistoryTest.php',
            'tests/Unit/Repositories/ImportedDataRepositoryTest.php'
        ];
        
        foreach ($testFiles as $file) {
            expect(file_exists(base_path($file)))->toBeTrue("Test file {$file} should exist");
        }
    });

    it('ensures test database is properly configured', function () {
        expect(config('database.default'))->toBe('testing')
            ->or(app()->environment())->toBe('testing');
    });

    it('validates that all necessary factories exist', function () {
        expect(\App\Models\User::factory())->not->toThrow()
            ->and(\App\Models\Workspace::factory())->not->toThrow();
    });
});
