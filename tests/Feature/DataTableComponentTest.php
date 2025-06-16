<?php

use App\Livewire\DataTable;
use App\Models\ImportedData;
use App\Models\ImportHistory;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Créer un utilisateur et un workspace
    $this->user = User::factory()->create();
    $this->workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
    $this->workspace->users()->attach($this->user->id, ['role' => 'owner']);
    
    // Authentifier l'utilisateur et définir le workspace courant
    $this->actingAs($this->user);
    session(['current_workspace_id' => $this->workspace->id]);
    
    // Créer un historique d'import
    $this->importHistory = ImportHistory::create([
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
    
    // Créer des données test
    ImportedData::create([
        'import_history_id' => $this->importHistory->id,
        'data' => ['name' => 'John Doe', 'email' => 'john@test.com', 'age' => 30],
        'row_hash' => 'hash1'
    ]);
    
    ImportedData::create([
        'import_history_id' => $this->importHistory->id,
        'data' => ['name' => 'Jane Smith', 'email' => 'jane@test.com', 'age' => 25],
        'row_hash' => 'hash2'
    ]);
});

it('can render the data table component', function () {
    Livewire::test(DataTable::class)
        ->assertStatus(200)
        ->assertSee('John Doe')
        ->assertSee('Jane Smith');
});

it('can search in the data table', function () {
    Livewire::test(DataTable::class)
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

it('can sort data in the table', function () {
    Livewire::test(DataTable::class)
        ->call('sortBy', 'name')
        ->assertSee('Jane Smith') // Should appear first when sorted by name ASC
        ->call('sortBy', 'name') // Second click should reverse to DESC
        ->assertSee('John Doe'); // Should appear first when sorted by name DESC
});

it('can delete a row', function () {
    $importedData = ImportedData::first();
    
    Livewire::test(DataTable::class)
        ->call('deleteRow', $importedData->id)
        ->assertHasNoErrors();
    
    expect(ImportedData::count())->toBe(1);
});

it('can select and delete multiple rows', function () {
    $importedData = ImportedData::all();
    
    Livewire::test(DataTable::class)
        ->set('selectedRows', $importedData->pluck('id')->toArray())
        ->call('deleteSelected')
        ->assertHasNoErrors();
    
    expect(ImportedData::count())->toBe(0);
});

it('can view row details', function () {
    $importedData = ImportedData::first();
    
    Livewire::test(DataTable::class)
        ->call('viewRow', $importedData->id)
        ->assertSet('showModal', true)
        ->assertSet('modalData', $importedData->data);
});

it('can edit a row', function () {
    $importedData = ImportedData::first();
    
    Livewire::test(DataTable::class)
        ->call('editRow', $importedData->id)
        ->assertSet('editingRow.id', $importedData->id)
        ->set('editData.name', 'Updated Name')
        ->call('saveEdit')
        ->assertHasNoErrors();
    
    expect($importedData->fresh()->data['name'])->toBe('Updated Name');
});

it('can filter by specific columns', function () {
    Livewire::test(DataTable::class)
        ->set('filters.name', 'John')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

it('can change pagination per page', function () {
    // Créer plus de données pour tester la pagination
    for ($i = 3; $i <= 20; $i++) {
        ImportedData::create([
            'import_history_id' => $this->importHistory->id,
            'data' => ['name' => "User {$i}", 'email' => "user{$i}@test.com"],
            'row_hash' => "hash{$i}"
        ]);
    }
    
    Livewire::test(DataTable::class)
        ->set('perPage', 5)
        ->assertSee('User 3')
        ->assertDontSee('User 10'); // Should not see beyond page 1
});
