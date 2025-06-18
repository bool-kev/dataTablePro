<?php

use App\Livewire\DataTable;
use App\Models\ImportedData;
use App\Models\ImportHistory;
use App\Models\User;
use App\Models\Workspace;
use Livewire\Livewire;

beforeEach(function () {
    ['user' => $this->user, 'workspace' => $this->workspace] = createUserWithWorkspace();
    actingAsUserInWorkspace($this->user, $this->workspace);
    
    // Créer un historique d'import
    $this->importHistory = createImportHistory($this->workspace);
    
    // Créer des données test variées
    $this->testData = [
        createImportedData($this->importHistory, ['name' => 'John Doe', 'email' => 'john@test.com', 'age' => 30, 'city' => 'Paris']),
        createImportedData($this->importHistory, ['name' => 'Jane Smith', 'email' => 'jane@test.com', 'age' => 25, 'city' => 'London']),
        createImportedData($this->importHistory, ['name' => 'Alice Johnson', 'email' => 'alice@test.com', 'age' => 35, 'city' => 'New York']),
    ];
});

describe('Component Rendering', function () {
    it('can render the data table component', function () {
        Livewire::test(DataTable::class)
            ->assertStatus(200)
            ->assertSee('John Doe')
            ->assertSee('Jane Smith')
            ->assertSee('Alice Johnson');
    });

    it('displays correct column headers', function () {
        Livewire::test(DataTable::class)
            ->assertSee('name')
            ->assertSee('email')
            ->assertSee('age')
            ->assertSee('city');
    });

    it('shows data statistics', function () {
        Livewire::test(DataTable::class)
            ->assertSee('3'); // Total count
    });

    it('handles empty data gracefully', function () {
        // Supprimer toutes les données
        ImportedData::query()->delete();
        
        Livewire::test(DataTable::class)
            ->assertSee('Aucune donnée trouvée')
            ->assertDontSee('John Doe');
    });
});

describe('Search Functionality', function () {
    it('can set filter column and value', function () {
        Livewire::test(DataTable::class)
            ->set('filterColumn', 'name')
            ->set('filterValue', 'John')
            ->assertSet('filterColumn', 'name')
            ->assertSet('filterValue', 'John');
    });

    it('can clear search', function () {
        Livewire::test(DataTable::class)
            ->set('filterValue', 'John')
            ->call('clearFilter')
            ->assertSet('filterValue', '')
            ->assertSet('filterColumn', 'all');
    });
});

describe('Sorting Functionality', function () {
    it('can sort data by column', function () {
        Livewire::test(DataTable::class)
            ->call('sortBy', 'name')
            ->assertSet('sortBy', 'name')
            ->assertSet('sortDirection', 'asc');
    });

    it('can reverse sort direction', function () {
        Livewire::test(DataTable::class)
            ->call('sortBy', 'name')
            ->call('sortBy', 'name') // Second click reverses order
            ->assertSet('sortDirection', 'desc');
    });

    it('can sort by column using sortByColumn method', function () {
        Livewire::test(DataTable::class)
            ->call('sortByColumn', 'age')
            ->assertSet('sortBy', 'age')
            ->assertSet('sortDirection', 'asc');
    });
});

describe('Row Operations', function () {
    it('can delete a single row', function () {
        $initialCount = ImportedData::count();
        
        Livewire::test(DataTable::class)
            ->call('deleteRow', $this->testData[0]->id)
            ->assertHasNoErrors();
        
        expect(ImportedData::count())->toBe($initialCount - 1);
    });

    it('can select rows for bulk operations', function () {
        Livewire::test(DataTable::class)
            ->set('selectedRows', [$this->testData[0]->id, $this->testData[1]->id])
            ->assertSet('selectedRows', [$this->testData[0]->id, $this->testData[1]->id]);
    });

    it('can delete multiple selected rows', function () {
        $selectedIds = [$this->testData[0]->id, $this->testData[1]->id];
        
        Livewire::test(DataTable::class)
            ->set('selectedRows', $selectedIds)
            ->call('deleteSelected')
            ->assertHasNoErrors();
        
        expect(ImportedData::count())->toBe(1);
    });
});

describe('Row Editing', function () {    it('can edit a row', function () {
        $importedData = $this->testData[0];
        
        $component = Livewire::test(DataTable::class)
            ->call('editRow', $importedData->id);
          // Vérifier que editingRow contient l'objet avec le bon ID
        expect($component->get('editingRow')->id)->toBe($importedData->id);
        
        $component->set('editData.name', 'Updated Name')
            ->call('saveEdit')
            ->assertHasNoErrors();
        
        expect($importedData->fresh()->data['name'])->toBe('Updated Name');
    });

    it('can cancel editing', function () {
        Livewire::test(DataTable::class)
            ->call('editRow', $this->testData[0]->id)
            ->set('editData.name', 'Changed Name')
            ->call('cancelEdit')
            ->assertSet('editingRow', null)
            ->assertSet('editData', []);
    });

    it('maintains data integrity during edit', function () {
        $originalData = $this->testData[0]->data;
        
        Livewire::test(DataTable::class)
            ->call('editRow', $this->testData[0]->id)
            ->set('editData.name', 'Updated Name')
            ->call('saveEdit');
        
        $updatedData = $this->testData[0]->fresh()->data;
        expect($updatedData['email'])->toBe($originalData['email'])
            ->and($updatedData['age'])->toBe($originalData['age']);
    });
});

describe('Row Details Modal', function () {
    it('can view row details', function () {
        $importedData = $this->testData[0];
        
        Livewire::test(DataTable::class)
            ->call('viewRow', $importedData->id)
            ->assertSet('showModal', true)
            ->assertSet('modalData', $importedData->data);
    });

    it('displays all row data in modal', function () {
        $importedData = $this->testData[0];
        
        Livewire::test(DataTable::class)
            ->call('viewRow', $importedData->id)
            ->assertSee($importedData->data['name'])
            ->assertSee($importedData->data['email'])
            ->assertSee($importedData->data['age']);
    });
});

describe('Export Functionality', function () {
    it('can export data as CSV', function () {
        Livewire::test(DataTable::class)
            ->call('exportCsv')
            ->assertHasNoErrors();
    });

    it('can export data as Excel', function () {
        Livewire::test(DataTable::class)
            ->call('exportExcel')
            ->assertHasNoErrors();
    });

    it('can export data as JSON', function () {
        Livewire::test(DataTable::class)
            ->call('exportJson')
            ->assertHasNoErrors();
    });
});

describe('Real-time Updates', function () {
    it('refreshes data when called', function () {
        $component = Livewire::test(DataTable::class);
        
        // Simuler un nouvel import
        $newImportHistory = createImportHistory($this->workspace);
        createImportedData($newImportHistory, ['name' => 'New User', 'email' => 'new@test.com']);
        
        $component->call('refreshData')
            ->assertSee('New User');
    });
});

describe('Workspace Isolation', function () {
    it('only shows data from current workspace', function () {
        // Créer un autre workspace avec des données
        ['user' => $otherUser, 'workspace' => $otherWorkspace] = createUserWithWorkspace();
        $otherImportHistory = createImportHistory($otherWorkspace);
        createImportedData($otherImportHistory, ['name' => 'Other User', 'email' => 'other@test.com']);
        
        Livewire::test(DataTable::class)
            ->assertSee('John Doe') // From current workspace
            ->assertDontSee('Other User'); // From other workspace
    });
});
