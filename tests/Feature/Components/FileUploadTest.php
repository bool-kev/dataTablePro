<?php

use App\Livewire\FileUpload;
use App\Models\ImportHistory;
use App\Services\ImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

describe('FileUpload Component', function () {
    beforeEach(function () {
        Storage::fake('public');
        
        ['user' => $this->user, 'workspace' => $this->workspace] = createUserWithWorkspace();
        actingAsUserInWorkspace($this->user, $this->workspace);
    });

    describe('Component Rendering', function () {        it('can render the file upload component', function () {
            Livewire::test(FileUpload::class)
                ->assertStatus(200)
                ->assertSee('Importer un fichier')
                ->assertSee('Sélectionner un fichier');
        });        it('shows supported file types', function () {
            Livewire::test(FileUpload::class)
                ->assertSee('CSV')
                ->assertSee('XLSX')
                ->assertSee('XLS');
        });        it('displays upload progress indicator', function () {
            Livewire::test(FileUpload::class)
                ->assertSet('uploadProgress', 0);
        });
    });

    describe('File Validation', function () {
        it('accepts valid CSV files', function () {
            $file = createCsvFile('valid.csv');
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->assertHasNoErrors('file');
        });

        it('accepts valid Excel files', function () {
            $file = createExcelFile('valid.xlsx');
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->assertHasNoErrors('file');
        });

        it('rejects invalid file types', function () {
            $file = UploadedFile::fake()->create('invalid.txt', 100, 'text/plain');
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->assertHasErrors('file');
        });

        it('validates file size limits', function () {
            $largeFile = UploadedFile::fake()->create('large.csv', 20000); // 20MB
            
            Livewire::test(FileUpload::class)
                ->set('file', $largeFile)
                ->assertHasErrors('file');
        });

        it('rejects empty files', function () {
            $emptyFile = UploadedFile::fake()->create('empty.csv', 0);
            
            Livewire::test(FileUpload::class)
                ->set('file', $emptyFile)
                ->assertHasErrors('file');
        });

        it('validates required file field', function () {
            Livewire::test(FileUpload::class)
                ->call('upload')
                ->assertHasErrors('file');
        });
    });

    describe('File Upload Process', function () {
        it('can upload and process a CSV file successfully', function () {
            $file = createCsvFile('test.csv');
            
            $component = Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->call('upload')
                ->assertHasNoErrors();
            
            expect($component->get('uploadSuccess'))->toBeTrue()
                ->and($component->get('uploadMessage'))->toContain('successfully uploaded')
                ->and(ImportHistory::count())->toBe(1);
        });

        it('updates upload progress during processing', function () {
            $file = createCsvFile('progress_test.csv');
            
            $component = Livewire::test(FileUpload::class)
                ->set('file', $file);
            
            // Simuler la progression
            $component->call('updateProgress', 50)
                ->assertSet('uploadProgress', 50);
        });

        it('shows success message after successful upload', function () {
            $file = createCsvFile('success_test.csv');
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->call('upload')
                ->assertSee('File uploaded successfully')
                ->assertSet('uploadSuccess', true);
        });

        it('resets form after successful upload', function () {
            $file = createCsvFile('reset_test.csv');
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->call('upload')
                ->assertSet('file', null)
                ->assertSet('uploadProgress', 0);
        });
    });

    describe('Error Handling', function () {
        it('handles file processing errors gracefully', function () {
            // Créer un fichier CSV malformé
            $corruptedFile = UploadedFile::fake()->createWithContent(
                'corrupted.csv',
                "name,email\nJohn,john@test.com\n\"unclosed quote"
            );
            
            Livewire::test(FileUpload::class)
                ->set('file', $corruptedFile)
                ->call('upload')
                ->assertSet('uploadError', true)
                ->assertSee('Error processing file');
        });

        it('shows specific error messages for different failure types', function () {
            // Mock du service pour simuler une erreur spécifique
            $this->mock(ImportService::class)
                ->shouldReceive('processFile')
                ->andThrow(new \InvalidArgumentException('Unsupported file format'));
            
            $file = createCsvFile('error_test.csv');
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->call('upload')
                ->assertSee('Unsupported file format');
        });

        it('handles storage errors', function () {
            // Simuler une erreur de stockage
            Storage::shouldReceive('disk')->andThrow(new \Exception('Storage unavailable'));
            
            $file = createCsvFile('storage_error.csv');
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->call('upload')
                ->assertSee('Storage error');
        });

        it('resets error state when new file is selected', function () {
            $file1 = createCsvFile('error_file.csv');
            $file2 = createCsvFile('good_file.csv');
            
            $component = Livewire::test(FileUpload::class)
                ->set('uploadError', true)
                ->set('errorMessage', 'Previous error')
                ->set('file', $file2);
            
            expect($component->get('uploadError'))->toBeFalse()
                ->and($component->get('errorMessage'))->toBeNull();
        });
    });

    describe('File Preview and Validation', function () {
        it('can preview file contents before upload', function () {
            $file = createCsvFile('preview.csv', [
                ['name', 'email', 'age'],
                ['John Doe', 'john@test.com', '30'],
                ['Jane Smith', 'jane@test.com', '25']
            ]);
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->call('previewFile')
                ->assertSee('John Doe')
                ->assertSee('Jane Smith')
                ->assertSet('showPreview', true);
        });

        it('can detect file encoding issues', function () {
            // Créer un fichier avec un encodage différent
            $content = mb_convert_encoding('name,email\nJosé,josé@test.com', 'ISO-8859-1', 'UTF-8');
            $file = UploadedFile::fake()->createWithContent('encoding_test.csv', $content);
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->call('validateFile')
                ->assertSee('encoding');
        });

        it('validates CSV structure', function () {
            $malformedCsv = createCsvFile('malformed.csv', [
                ['name', 'email'],
                ['John Doe'],  // Missing email
                ['Jane Smith', 'jane@test.com', 'extra_field']  // Extra field
            ]);
            
            Livewire::test(FileUpload::class)
                ->set('file', $malformedCsv)
                ->call('validateFile')
                ->assertSee('inconsistent columns');
        });

        it('can detect and warn about duplicate data', function () {
            $duplicateData = createCsvFile('duplicates.csv', [
                ['name', 'email'],
                ['John Doe', 'john@test.com'],
                ['John Doe', 'john@test.com'],  // Duplicate
                ['Jane Smith', 'jane@test.com']
            ]);
            
            Livewire::test(FileUpload::class)
                ->set('file', $duplicateData)
                ->call('validateFile')
                ->assertSee('duplicate rows detected');
        });
    });

    describe('Import Configuration', function () {
        it('can configure import settings before upload', function () {
            $file = createCsvFile('config_test.csv');
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->set('importConfig.skipDuplicates', true)
                ->set('importConfig.delimiter', ';')
                ->call('upload')
                ->assertHasNoErrors();
        });

        it('can map CSV columns to database fields', function () {
            $file = createCsvFile('mapping_test.csv', [
                ['full_name', 'email_address', 'years_old'],
                ['John Doe', 'john@test.com', '30']
            ]);
            
            $columnMapping = [
                'full_name' => 'name',
                'email_address' => 'email',
                'years_old' => 'age'
            ];
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->set('columnMapping', $columnMapping)
                ->call('upload')
                ->assertHasNoErrors();
        });

        it('can set data transformation rules', function () {
            $file = createCsvFile('transform_test.csv');
            
            $transformRules = [
                'email' => 'lowercase',
                'name' => 'title_case',
                'phone' => 'format_phone'
            ];
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->set('transformRules', $transformRules)
                ->call('upload')
                ->assertHasNoErrors();
        });
    });

    describe('Real-time Feedback', function () {
        it('provides real-time row count as file is processed', function () {
            $largeFile = createCsvFile('large_file.csv');
            
            // Ajouter beaucoup de lignes
            for ($i = 1; $i <= 100; $i++) {
                // Simuler l'ajout de lignes
            }
            
            $component = Livewire::test(FileUpload::class)
                ->set('file', $largeFile)
                ->call('upload');
            
            expect($component->get('processedRows'))->toBeGreaterThan(0);
        });

        it('shows estimated time remaining for large files', function () {
            $largeFile = createCsvFile('time_estimate.csv');
            
            Livewire::test(FileUpload::class)
                ->set('file', $largeFile)
                ->call('startUpload')
                ->assertSee('Estimated time remaining');
        });

        it('can cancel upload in progress', function () {
            $file = createCsvFile('cancel_test.csv');
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->call('startUpload')
                ->call('cancelUpload')
                ->assertSet('uploadCancelled', true)
                ->assertSet('uploadInProgress', false);
        });
    });

    describe('Batch Upload Support', function () {
        it('can handle multiple file uploads', function () {
            $files = [
                createCsvFile('batch1.csv'),
                createCsvFile('batch2.csv'),
                createCsvFile('batch3.csv')
            ];
            
            Livewire::test(FileUpload::class)
                ->set('files', $files)
                ->call('uploadBatch')
                ->assertHasNoErrors();
            
            expect(ImportHistory::count())->toBe(3);
        });

        it('provides progress for batch uploads', function () {
            $files = [createCsvFile('batch1.csv'), createCsvFile('batch2.csv')];
            
            $component = Livewire::test(FileUpload::class)
                ->set('files', $files)
                ->call('uploadBatch');
            
            expect($component->get('batchProgress'))->toBeArray()
                ->and($component->get('batchProgress'))->toHaveCount(2);
        });

        it('continues batch upload even if one file fails', function () {
            $files = [
                createCsvFile('good1.csv'),
                UploadedFile::fake()->create('bad.txt', 100, 'text/plain'), // Invalid
                createCsvFile('good2.csv')
            ];
            
            Livewire::test(FileUpload::class)
                ->set('files', $files)
                ->call('uploadBatch');
            
            expect(ImportHistory::count())->toBe(2); // Only good files processed
        });
    });

    describe('User Experience', function () {
        it('shows file size and estimated processing time', function () {
            $file = createCsvFile('info_test.csv');
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->assertSee('File size')
                ->assertSee('Estimated processing time');
        });

        it('provides drag and drop visual feedback', function () {
            Livewire::test(FileUpload::class)
                ->assertSee('Drag and drop')
                ->assertSee('drop-zone');
        });

        it('shows upload history', function () {
            // Créer quelques imports précédents
            createImportHistory($this->workspace, ['original_filename' => 'previous1.csv']);
            createImportHistory($this->workspace, ['original_filename' => 'previous2.csv']);
            
            Livewire::test(FileUpload::class)
                ->call('showUploadHistory')
                ->assertSee('previous1.csv')
                ->assertSee('previous2.csv');
        });

        it('can retry failed uploads', function () {
            $file = createCsvFile('retry_test.csv');
            
            $component = Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->set('uploadError', true)
                ->call('retryUpload');
            
            expect($component->get('uploadError'))->toBeFalse();
        });
    });

    describe('Workspace Integration', function () {
        it('respects workspace permissions', function () {
            // Créer un utilisateur sans permissions
            $unauthorizedUser = \App\Models\User::factory()->create();
            $this->actingAs($unauthorizedUser);
            
            Livewire::test(FileUpload::class)
                ->assertStatus(403);
        });

        it('stores uploads in workspace context', function () {
            $file = createCsvFile('workspace_test.csv');
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->call('upload');
            
            $importHistory = ImportHistory::first();
            expect($importHistory->workspace_id)->toBe($this->workspace->id);
        });

        it('shows workspace storage quota', function () {
            Livewire::test(FileUpload::class)
                ->assertSee('Storage used')
                ->assertSee('MB');
        });
    });
});
