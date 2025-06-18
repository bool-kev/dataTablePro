<?php

use App\Models\ImportHistory;
use App\Models\ImportedData;
use App\Models\User;
use App\Models\Workspace;

describe('ImportHistory Model', function () {
    beforeEach(function () {
        ['user' => $this->user, 'workspace' => $this->workspace] = createUserWithWorkspace();
    });

    describe('Model Attributes', function () {
        it('has correct fillable attributes', function () {
            $fillable = (new ImportHistory())->getFillable();
            
            expect($fillable)->toContain(
                'workspace_id',
                'filename',
                'original_filename',
                'file_path',
                'file_type',
                'status',
                'total_rows',
                'successful_rows',
                'failed_rows',
                'errors',
                'started_at',
                'completed_at'
            );
        });

        it('casts dates correctly', function () {
            $importHistory = createImportHistory($this->workspace, [
                'started_at' => '2023-01-15 10:30:00',
                'completed_at' => '2023-01-15 10:35:00'
            ]);
            
            expect($importHistory->started_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
                ->and($importHistory->completed_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        });

        it('casts errors to array', function () {
            $importHistory = createImportHistory($this->workspace, [
                'errors' => ['Error 1', 'Error 2']
            ]);
            
            expect($importHistory->errors)->toBeArray()
                ->and($importHistory->errors)->toContain('Error 1', 'Error 2');
        });
    });

    describe('Relationships', function () {
        it('belongs to a workspace', function () {
            $importHistory = createImportHistory($this->workspace);
            
            expect($importHistory->workspace)->toBeInstanceOf(Workspace::class)
                ->and($importHistory->workspace->id)->toBe($this->workspace->id);
        });

        it('has many imported data records', function () {
            $importHistory = createImportHistory($this->workspace);
            
            createImportedData($importHistory, ['name' => 'John']);
            createImportedData($importHistory, ['name' => 'Jane']);
            
            expect($importHistory->importedData)->toHaveCount(2)
                ->and($importHistory->importedData->first())->toBeInstanceOf(ImportedData::class);
        });
    });

    describe('Success Rate Calculation', function () {
        it('calculates success rate correctly', function () {
            $importHistory = createImportHistory($this->workspace, [
                'total_rows' => 100,
                'successful_rows' => 85,
                'failed_rows' => 15
            ]);
            
            expect($importHistory->success_rate)->toBe(85.0);
        });

        it('handles zero total rows for success rate', function () {
            $importHistory = createImportHistory($this->workspace, [
                'total_rows' => 0,
                'successful_rows' => 0,
                'failed_rows' => 0
            ]);
            
            expect($importHistory->success_rate)->toBe(0.0);
        });
    });

    describe('Workspace Scoping', function () {
        it('filters by workspace correctly', function () {
            $import1 = createImportHistory($this->workspace);
            
            ['workspace' => $otherWorkspace] = createUserWithWorkspace();
            $import2 = createImportHistory($otherWorkspace);
            
            $results = ImportHistory::forWorkspace($this->workspace)->get();
            
            expect($results)->toHaveCount(1)
                ->and($results->first()->id)->toBe($import1->id);
        });
    });
});
