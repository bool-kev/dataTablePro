<?php

use App\Models\ImportedData;
use App\Models\ImportHistory;
use App\Models\Workspace;

describe('ImportedData Model', function () {
    beforeEach(function () {
        ['user' => $this->user, 'workspace' => $this->workspace] = createUserWithWorkspace();
        $this->importHistory = createImportHistory($this->workspace);
    });

    describe('Attributes and Casting', function () {
        it('casts data attribute to array', function () {
            $data = ['name' => 'John', 'email' => 'john@test.com'];
            
            $importedData = ImportedData::create([
                'import_history_id' => $this->importHistory->id,
                'data' => $data,
                'row_hash' => 'test_hash'
            ]);
            
            expect($importedData->data)->toBeArray()
                ->and($importedData->data)->toBe($data);
        });

        it('automatically generates row_hash when setting data', function () {
            $data = ['name' => 'John', 'email' => 'john@test.com'];
            
            $importedData = new ImportedData();
            $importedData->data = $data;
            
            expect($importedData->row_hash)->toBe(md5(json_encode($data)));
        });

        it('generates consistent hash for same data', function () {
            $data = ['name' => 'John', 'email' => 'john@test.com'];
            
            $importedData1 = new ImportedData();
            $importedData1->data = $data;
            
            $importedData2 = new ImportedData();
            $importedData2->data = $data;
            
            expect($importedData1->row_hash)->toBe($importedData2->row_hash);
        });

        it('generates different hash for different data', function () {
            $data1 = ['name' => 'John', 'email' => 'john@test.com'];
            $data2 = ['name' => 'Jane', 'email' => 'jane@test.com'];
            
            $importedData1 = new ImportedData();
            $importedData1->data = $data1;
            
            $importedData2 = new ImportedData();
            $importedData2->data = $data2;
            
            expect($importedData1->row_hash)->not->toBe($importedData2->row_hash);
        });
    });

    describe('Relationships', function () {
        it('belongs to import history', function () {
            $importedData = createImportedData($this->importHistory);
            
            expect($importedData->importHistory)->toBeInstanceOf(ImportHistory::class)
                ->and($importedData->importHistory->id)->toBe($this->importHistory->id);
        });

        it('has workspace through import history', function () {
            $importedData = createImportedData($this->importHistory);
            
            expect($importedData->importHistory->workspace)->toBeInstanceOf(Workspace::class)
                ->and($importedData->importHistory->workspace->id)->toBe($this->workspace->id);
        });
    });

    describe('Scopes', function () {
        it('can filter by workspace', function () {
            // Créer des données pour ce workspace
            $importedData1 = createImportedData($this->importHistory);
            
            // Créer un autre workspace avec des données
            ['workspace' => $otherWorkspace] = createUserWithWorkspace();
            $otherImportHistory = createImportHistory($otherWorkspace);
            $importedData2 = createImportedData($otherImportHistory);
            
            $results = ImportedData::forWorkspace($this->workspace)->get();
            
            expect($results)->toHaveCount(1)
                ->and($results->first()->id)->toBe($importedData1->id);
        });

        it('returns empty collection for workspace with no data', function () {
            ['workspace' => $emptyWorkspace] = createUserWithWorkspace();
            
            $results = ImportedData::forWorkspace($emptyWorkspace)->get();
            
            expect($results)->toHaveCount(0);
        });
    });

    describe('Data Validation', function () {
        it('requires import_history_id', function () {
            expect(function () {
                ImportedData::create([
                    'data' => ['name' => 'John'],
                    'row_hash' => 'test'
                ]);
            })->toThrow(\Illuminate\Database\QueryException::class);
        });

        it('can handle complex data structures', function () {
            $complexData = [
                'name' => 'John',
                'contacts' => [
                    'email' => 'john@test.com',
                    'phone' => '123456789'
                ],
                'tags' => ['customer', 'vip'],
                'metadata' => [
                    'created_by' => 'import_system',
                    'confidence' => 0.95
                ]
            ];
              $importedData = ImportedData::create([
                'import_history_id' => $this->importHistory->id,
                'data' => $complexData,
                'row_hash' => md5(json_encode($complexData))
            ]);
            
            expect($importedData->data)->toBe($complexData)
                ->and($importedData->data['contacts']['email'])->toBe('john@test.com')
                ->and($importedData->data['tags'])->toContain('customer', 'vip');
        });

        it('handles empty data gracefully', function () {
            $importedData = ImportedData::create([
                'import_history_id' => $this->importHistory->id,
                'data' => [],
                'row_hash' => md5(json_encode([]))
            ]);

            expect($importedData->data)->toBe([])
                ->and($importedData->row_hash)->not->toBeNull();
        });

        it('handles null values in data', function () {
            $dataWithNulls = [
                'name' => 'John',
                'middle_name' => null,
                'age' => 30,
                'notes' => null
            ];
            
            $importedData = createImportedData($this->importHistory, $dataWithNulls);
            
            expect($importedData->data['middle_name'])->toBeNull()
                ->and($importedData->data['notes'])->toBeNull()
                ->and($importedData->data['name'])->toBe('John');
        });
    });

    describe('JSON Handling', function () {
        it('properly encodes and decodes UTF-8 characters', function () {
            $utf8Data = [
                'name' => 'José María',
                'city' => 'São Paulo',
                'notes' => 'Spécialisé en développement'
            ];
              $importedData = ImportedData::create([
                'import_history_id' => $this->importHistory->id,
                'data' => $utf8Data,
                'row_hash' => md5(json_encode($utf8Data))
            ]);

            expect($importedData->fresh()->data)->toBe($utf8Data);
        });

        it('handles special characters and symbols', function () {
            $specialData = [
                'name' => 'John & Jane',
                'email' => 'test+user@example.com',
                'notes' => 'Symbols: @#$%^&*()_+-={}[]|\\:";\'<>?,./',
                'unicode' => '🚀 🎉 ✨'
            ];
              $importedData = ImportedData::create([
                'import_history_id' => $this->importHistory->id,
                'data' => $specialData,
                'row_hash' => md5(json_encode($specialData))
            ]);
            
            expect($importedData->fresh()->data)->toBe($specialData);
        });
    });

    describe('Performance', function () {
        it('can handle large data objects efficiently', function () {
            // Créer un objet de données volumineux
            $largeData = [
                'name' => 'Performance Test User',
                'description' => str_repeat('Lorem ipsum dolor sit amet, ', 1000)
            ];
            
            $startTime = microtime(true);
            $importedData = createImportedData($this->importHistory, $largeData);
            $endTime = microtime(true);
            
            expect($endTime - $startTime)->toBeLessThan(1.0) // Moins d'une seconde
                ->and($importedData->data['name'])->toBe('Performance Test User');
        });

        it('maintains consistent performance with multiple operations', function () {
            $times = [];
            
            for ($i = 0; $i < 10; $i++) {
                $data = ['name' => "User $i", 'index' => $i];
                
                $startTime = microtime(true);
                createImportedData($this->importHistory, $data);
                $endTime = microtime(true);
                
                $times[] = $endTime - $startTime;
            }
            
            $avgTime = array_sum($times) / count($times);
            expect($avgTime)->toBeLessThan(0.1); // Moins de 100ms en moyenne
        });
    });
});
