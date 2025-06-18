<?php

use App\Models\ImportedData;
use App\Models\ImportHistory;
use App\Repositories\ImportedDataRepository;

describe('ImportedDataRepository', function () {
    beforeEach(function () {
        ['user' => $this->user, 'workspace' => $this->workspace] = createUserWithWorkspace();
        $this->repository = app(ImportedDataRepository::class);
        $this->importHistory = createImportHistory($this->workspace);
    });

    describe('Basic CRUD Operations', function () {
        it('can create imported data', function () {
            $data = ['name' => 'John', 'email' => 'john@test.com'];
            
            $importedData = $this->repository->create([
                'import_history_id' => $this->importHistory->id,
                'data' => $data,
                'row_hash' => md5(json_encode($data))
            ]);
            
            expect($importedData)->toBeInstanceOf(ImportedData::class)
                ->and($importedData->data)->toBe($data)
                ->and($importedData->exists)->toBeTrue();
        });

        it('can find by id', function () {
            $importedData = createImportedData($this->importHistory);

            $found = $this->repository->findById($importedData->id);

            expect($found)->toBeInstanceOf(ImportedData::class)
                ->and($found->id)->toBe($importedData->id);
        });

        it('can update imported data', function () {
            $importedData = createImportedData($this->importHistory);
            $newData = ['name' => 'Updated John', 'email' => 'john.updated@test.com'];
            
            $updated = $this->repository->update($importedData, [
                'data' => $newData,
                'row_hash' => md5(json_encode($newData))
            ]);
            
            expect($updated)->toBeTrue();
            
            $refreshed = $importedData->fresh();
            expect($refreshed->data)->toBe($newData);
        });

        it('can delete imported data', function () {
            $importedData = createImportedData($this->importHistory);
            
            $deleted = $this->repository->delete($importedData);
            
            expect($deleted)->toBeTrue()
                ->and($this->repository->findById($importedData->id))->toBeNull();
        });
    });

    describe('Workspace Filtering', function () {
        it('gets data for specific workspace only', function () {
            // Créer des données pour ce workspace
            $data1 = createImportedData($this->importHistory, ['name' => 'John']);
            
            // Créer un autre workspace avec des données
            ['workspace' => $otherWorkspace] = createUserWithWorkspace();
            $otherImportHistory = createImportHistory($otherWorkspace);
            $data2 = createImportedData($otherImportHistory, ['name' => 'Jane']);
            
            $results = $this->repository->paginate(15, null, 'id', 'desc', [], $this->workspace);
            
            expect($results)->toHaveCount(1)
                ->and($results->first()->id)->toBe($data1->id);
        });

        it('returns paginated results for workspace', function () {
            // Créer plusieurs données
            for ($i = 1; $i <= 25; $i++) {
                createImportedData($this->importHistory, ['name' => "User $i"]);
            }
            
            $results = $this->repository->paginate(10, null, 'id', 'desc', [], $this->workspace);
            
            expect($results->count())->toBe(10)
                ->and($results->total())->toBe(25)
                ->and($results->hasPages())->toBeTrue();
        });
    });

    describe('Search and Filtering', function () {
        beforeEach(function () {
            createImportedData($this->importHistory, ['name' => 'John Doe', 'email' => 'john@test.com', 'city' => 'Paris']);
            createImportedData($this->importHistory, ['name' => 'Jane Smith', 'email' => 'jane@test.com', 'city' => 'London']);
            createImportedData($this->importHistory, ['name' => 'Bob Johnson', 'email' => 'bob@example.org', 'city' => 'New York']);
        });

        it('can search across all data fields', function () {
            $results = $this->repository->paginate(15, 'john', 'id', 'desc', [], $this->workspace);

            expect($results->count())->toBe(2); // John Doe et Bob Johnson
        });

        it('search is case insensitive', function () {
            $results = $this->repository->paginate(15, 'JOHN', 'id', 'desc', [], $this->workspace);

            expect($results->count())->toBe(2);
        });

        it('can search by email domain', function () {
            $results = $this->repository->paginate(15, 'test.com', 'id', 'desc', [], $this->workspace);

            expect($results->count())->toBe(2); // John et Jane
        });

        it('can filter by specific column values', function () {
            $filters = ['city' => 'Paris'];
            $results = $this->repository->paginate(15, null, 'id', 'desc', $filters, $this->workspace);

            expect($results->count())->toBe(1)
                ->and($results->first()->data['name'])->toBe('John Doe');
        });

        it('can apply multiple filters', function () {
            $filters = ['city' => 'London', 'name' => 'Jane'];
            $results = $this->repository->paginate(15, null, 'id', 'desc', $filters, $this->workspace);

            expect($results->count())->toBe(1)
                ->and($results->first()->data['name'])->toBe('Jane Smith');
        });

        it('returns empty results for non-matching filters', function () {
            $filters = ['city' => 'Tokyo'];
            $results = $this->repository->paginate(15, null, 'id', 'desc', $filters, $this->workspace);

            expect($results->count())->toBe(0);
        });
    });

    describe('Sorting', function () {
        beforeEach(function () {
            createImportedData($this->importHistory, ['name' => 'Charlie', 'age' => 35]);
            createImportedData($this->importHistory, ['name' => 'Alice', 'age' => 25]);
            createImportedData($this->importHistory, ['name' => 'Bob', 'age' => 30]);
        });

        it('can sort by name ascending', function () {
            $results = $this->repository->paginate(15, null, 'name', 'asc', [], $this->workspace);

            expect($results->first()->data['name'])->toBe('Alice')
                ->and($results->last()->data['name'])->toBe('Charlie');
        });

        it('can sort by name descending', function () {
            $results = $this->repository->paginate(15, null, 'name', 'desc', [], $this->workspace);

            expect($results->first()->data['name'])->toBe('Charlie')
                ->and($results->last()->data['name'])->toBe('Alice');
        });

        it('can sort by numeric fields', function () {
            $results = $this->repository->paginate(15, null, 'age', 'asc', [], $this->workspace);

            expect($results->first()->data['age'])->toBe(25)
                ->and($results->last()->data['age'])->toBe(35);
        });

        it('handles null values in sorting', function () {
            createImportedData($this->importHistory, ['name' => 'David']); // age is null

            $results = $this->repository->paginate(15, null, 'age', 'asc', [], $this->workspace);

            expect($results->count())->toBe(4);
        });
    });

    describe('Column Analysis', function () {
        beforeEach(function () {
            createImportedData($this->importHistory, ['name' => 'John', 'email' => 'john@test.com', 'age' => 30]);
            createImportedData($this->importHistory, ['name' => 'Jane', 'phone' => '123456789', 'city' => 'Paris']);
            createImportedData($this->importHistory, ['title' => 'Manager', 'department' => 'IT']);
        });

        it('can get unique columns from workspace data', function () {
            $columns = $this->repository->getUniqueColumns($this->workspace);
              expect($columns)->toContain('name', 'email', 'age', 'phone', 'city', 'title', 'department')
                ->and($columns)->toHaveCount(8); // Inclut unique_id ajouté par le helper
        });

        it('returns empty array for workspace with no data', function () {
            ['workspace' => $emptyWorkspace] = createUserWithWorkspace();
            
            $columns = $this->repository->getUniqueColumns($emptyWorkspace);
            
            expect($columns)->toBe([]);
        });

        it('can get column statistics', function () {
            $stats = $this->repository->getColumnStats($this->workspace, 'name');
            
            expect($stats)->toHaveKey('total_count')
                ->and($stats)->toHaveKey('non_null_count')
                ->and($stats['non_null_count'])->toBe(3); // 3 car createImportedData ajoute toujours 'name'
        });

        it('can detect column data types', function () {
            $types = $this->repository->detectColumnTypes($this->workspace);
              expect($types['age'])->toBe('numeric')
                ->and($types['name'])->toBe('text');
        });
    });

    describe('Bulk Operations', function () {
        it('can bulk insert data efficiently', function () {
            $bulkData = [];
            for ($i = 1; $i <= 100; $i++) {
                $data = ['name' => "User $i", 'email' => "user$i@test.com"];
                $bulkData[] = [
                    'import_history_id' => $this->importHistory->id,
                    'data' => $data,
                    'row_hash' => md5(json_encode($data)),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            $startTime = microtime(true);
            $result = $this->repository->bulkInsert($bulkData);
            $endTime = microtime(true);
            
            expect($result)->toBeTrue()
                ->and($endTime - $startTime)->toBeLessThan(2.0) // Moins de 2 secondes
                ->and(ImportedData::count())->toBe(100);
        });

        it('can bulk delete by ids', function () {
            $data1 = createImportedData($this->importHistory);
            $data2 = createImportedData($this->importHistory);
            $data3 = createImportedData($this->importHistory);
            
            $deleted = $this->repository->bulkDelete([$data1->id, $data2->id]);
            
            expect($deleted)->toBe(2)
                ->and(ImportedData::count())->toBe(1)
                ->and(ImportedData::find($data3->id))->not->toBeNull();
        });

        it('can bulk update data', function () {
            $data1 = createImportedData($this->importHistory, ['status' => 'pending']);
            $data2 = createImportedData($this->importHistory, ['status' => 'pending']);
            
            $updated = $this->repository->bulkUpdate(
                [$data1->id, $data2->id],
                ['status' => 'processed']
            );
            
            expect($updated)->toBe(2);
            
            $data1->refresh();
            $data2->refresh();
            
            expect($data1->data['status'])->toBe('processed')
                ->and($data2->data['status'])->toBe('processed');
        });
    });

    describe('Duplicate Detection', function () {
        it('can detect duplicates by hash', function () {
            $data = ['name' => 'John', 'email' => 'john@test.com'];
            
            $importedData = createImportedData($this->importHistory, $data);
            $isDuplicate = $this->repository->isDuplicate($importedData->row_hash, $this->workspace);
            
            expect($isDuplicate)->toBeTrue();
        });

        it('returns false for unique data', function () {
            $data = ['name' => 'Unique User', 'email' => 'unique@test.com'];
            $hash = md5(json_encode($data));
            
            $isDuplicate = $this->repository->isDuplicate($hash, $this->workspace);
            
            expect($isDuplicate)->toBeFalse();
        });        it('can find duplicates within workspace', function () {
            // Créer des données avec différents contenus mais des hashes similaires pour test
            $data1 = ['name' => 'John', 'email' => 'john@test.com'];
            $data2 = ['name' => 'John Smith', 'email' => 'johnsmith@test.com'];
            
            createImportedData($this->importHistory, $data1);
            createImportedData($this->importHistory, $data2);
            createImportedData($this->importHistory, ['name' => 'Jane']);
            
            // Cette méthode devrait chercher des contenus similaires
            // Pour l'instant, test que la méthode existe et retourne une collection
            $duplicates = $this->repository->findDuplicates($this->workspace);
            
            expect($duplicates)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
        });
    });

    describe('Export Support', function () {
        it('can prepare data for CSV export', function () {
            createImportedData($this->importHistory, ['name' => 'John', 'email' => 'john@test.com']);
            createImportedData($this->importHistory, ['name' => 'Jane', 'email' => 'jane@test.com']);
            
            $exportData = $this->repository->getForExport($this->workspace, ['name', 'email']);
            
            expect($exportData)->toHaveCount(2)
                ->and($exportData[0])->toHaveKey('name')
                ->and($exportData[0])->toHaveKey('email')
                ->and($exportData[0])->not->toHaveKey('id');
        });

        it('can export with custom column selection', function () {
            createImportedData($this->importHistory, ['name' => 'John', 'email' => 'john@test.com', 'age' => 30]);
            
            $exportData = $this->repository->getForExport($this->workspace, ['name']);
            
            expect($exportData[0])->toHaveKey('name')
                ->and($exportData[0])->not->toHaveKey('email')
                ->and($exportData[0])->not->toHaveKey('age');
        });
    });

    describe('Performance Optimization', function () {
        it('uses proper indexing for searches', function () {
            // Créer beaucoup de données
            for ($i = 1; $i <= 1000; $i++) {
                createImportedData($this->importHistory, ['name' => "User $i", 'email' => "user$i@test.com"]);
            }
              $startTime = microtime(true);
            $results = $this->repository->paginate(15, 'User 500', 'id', 'desc', [], $this->workspace);
            $endTime = microtime(true);

            expect($endTime - $startTime)->toBeLessThan(1.0) // Moins d'une seconde
                ->and($results->count())->toBe(1);
        });

        it('efficiently handles large result sets with pagination', function () {
            // Créer beaucoup de données
            for ($i = 1; $i <= 500; $i++) {
                createImportedData($this->importHistory, ['name' => "User $i"]);
            }
              $startTime = microtime(true);
            $results = $this->repository->paginate(50, null, 'id', 'desc', [], $this->workspace);
            $endTime = microtime(true);

            expect($endTime - $startTime)->toBeLessThan(0.5) // Moins de 500ms
                ->and($results->count())->toBe(50);
        });
    });
});
