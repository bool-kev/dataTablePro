<?php

use App\Models\ImportedData;
use App\Repositories\ImportedDataRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(ImportedDataRepository::class);
});

it('can paginate imported data', function () {
    // Créer des données test
    for ($i = 1; $i <= 25; $i++) {
        ImportedData::create([
            'import_history_id' => 1,
            'data' => ['name' => "User {$i}", 'email' => "user{$i}@test.com"],
            'row_hash' => "hash{$i}"
        ]);
    }
    
    $result = $this->repository->paginate(10);
    
    expect($result->count())->toBe(10);
    expect($result->total())->toBe(25);
});

it('can search in JSON data', function () {
    ImportedData::create([
        'import_history_id' => 1,
        'data' => ['name' => 'John Doe', 'email' => 'john@test.com'],
        'row_hash' => 'hash1'
    ]);
    
    ImportedData::create([
        'import_history_id' => 1,
        'data' => ['name' => 'Jane Smith', 'email' => 'jane@test.com'],
        'row_hash' => 'hash2'
    ]);
    
    $result = $this->repository->paginate(10, 'John');
    
    expect($result->count())->toBe(1);
    expect($result->first()->data['name'])->toBe('John Doe');
});

it('can filter by specific columns', function () {
    ImportedData::create([
        'import_history_id' => 1,
        'data' => ['category' => 'A', 'value' => 100],
        'row_hash' => 'hash1'
    ]);
    
    ImportedData::create([
        'import_history_id' => 1,
        'data' => ['category' => 'B', 'value' => 200],
        'row_hash' => 'hash2'
    ]);
    
    $result = $this->repository->paginate(10, null, 'id', 'desc', ['category' => 'A']);
    
    expect($result->count())->toBe(1);
    expect($result->first()->data['category'])->toBe('A');
});

it('can sort by JSON columns', function () {
    ImportedData::create([
        'import_history_id' => 1,
        'data' => ['name' => 'Charlie', 'age' => 30],
        'row_hash' => 'hash1'
    ]);
    
    ImportedData::create([
        'import_history_id' => 1,
        'data' => ['name' => 'Alice', 'age' => 25],
        'row_hash' => 'hash2'
    ]);
    
    ImportedData::create([
        'import_history_id' => 1,
        'data' => ['name' => 'Bob', 'age' => 35],
        'row_hash' => 'hash3'
    ]);
    
    $result = $this->repository->paginate(10, null, 'name', 'asc');
    
    expect($result->first()->data['name'])->toBe('Alice');
});

it('can get unique columns from all data', function () {
    ImportedData::create([
        'import_history_id' => 1,
        'data' => ['name' => 'John', 'email' => 'john@test.com'],
        'row_hash' => 'hash1'
    ]);
    
    ImportedData::create([
        'import_history_id' => 1,
        'data' => ['name' => 'Jane', 'phone' => '123456789'],
        'row_hash' => 'hash2'
    ]);
    
    $columns = $this->repository->getUniqueColumns();
    
    expect($columns)->toContain('name', 'email', 'phone');
    expect(count($columns))->toBe(3);
});
