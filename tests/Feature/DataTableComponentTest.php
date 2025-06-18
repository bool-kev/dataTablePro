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
            ->assertSee('3') // Total count
            ->assertViewHas('data');
    });

    it('handles empty data gracefully', function () {
        // Supprimer toutes les données
        ImportedData::query()->delete();
        
        Livewire::test(DataTable::class)
            ->assertSee('No data found')
            ->assertDontSee('John Doe');
    });
});

describe('Search Functionality', function () {
    it('can search in the data table', function () {
        Livewire::test(DataTable::class)
            ->set('search', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith')
            ->assertDontSee('Alice Johnson');
    });

    it('can search across multiple columns', function () {
        Livewire::test(DataTable::class)
            ->set('search', 'test.com')
            ->assertSee('John Doe')
            ->assertSee('Jane Smith')
            ->assertSee('Alice Johnson');
    });

    it('search is case insensitive', function () {
        Livewire::test(DataTable::class)
            ->set('search', 'JOHN')
            ->assertSee('John Doe');
    });

    it('can clear search', function () {
        Livewire::test(DataTable::class)
            ->set('search', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith')
            ->set('search', '')
            ->assertSee('John Doe')
            ->assertSee('Jane Smith');
    });

    it('shows no results message when search yields nothing', function () {
        Livewire::test(DataTable::class)
            ->set('search', 'nonexistent')
            ->assertSee('No data found')
            ->assertDontSee('John Doe');
    });
});

describe('Sorting Functionality', function () {
    it('can sort data by name ascending', function () {
        $component = Livewire::test(DataTable::class)
            ->call('sortBy', 'name');
        
        $data = $component->get('data');
        expect($data->first()['name'])->toBe('Alice Johnson');
    });

    it('can sort data by name descending', function () {
        $component = Livewire::test(DataTable::class)
            ->call('sortBy', 'name')
            ->call('sortBy', 'name'); // Second click reverses order
        
        $data = $component->get('data');
        expect($data->first()['name'])->toBe('John Doe');
    });

    it('can sort by age numerically', function () {
        $component = Livewire::test(DataTable::class)
            ->call('sortBy', 'age');
        
        $data = $component->get('data');
        expect($data->first()['age'])->toBe(25); // Jane Smith
    });

    it('maintains sort direction indicator', function () {
        Livewire::test(DataTable::class)
            ->call('sortBy', 'name')
            ->assertSet('sortField', 'name')
            ->assertSet('sortDirection', 'asc');
    });
});

describe('Filtering Functionality', function () {
    it('can filter by specific columns', function () {
        Livewire::test(DataTable::class)
            ->set('filters.name', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith');
    });

    it('can filter by multiple columns simultaneously', function () {
        Livewire::test(DataTable::class)
            ->set('filters.city', 'Paris')
            ->set('filters.age', '30')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith')
            ->assertDontSee('Alice Johnson');
    });

    it('can clear individual filters', function () {
        Livewire::test(DataTable::class)
            ->set('filters.name', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith')
            ->set('filters.name', '')
            ->assertSee('John Doe')
            ->assertSee('Jane Smith');
    });

    it('can clear all filters at once', function () {
        Livewire::test(DataTable::class)
            ->set('filters.name', 'John')
            ->set('filters.city', 'Paris')
            ->call('clearFilters')
            ->assertSee('John Doe')
            ->assertSee('Jane Smith')
            ->assertSee('Alice Johnson');
    });
});

describe('Row Operations', function () {
    it('can delete a single row', function () {
        $importedData = $this->testData[0];
        
        Livewire::test(DataTable::class)
            ->call('deleteRow', $importedData->id)
            ->assertHasNoErrors();
        
        expect(ImportedData::count())->toBe(2)
            ->and(ImportedData::find($importedData->id))->toBeNull();
    });

    it('can select rows for bulk operations', function () {
        Livewire::test(DataTable::class)
            ->set('selectedRows', [$this->testData[0]->id, $this->testData[1]->id])
            ->assertSet('selectedRows', [$this->testData[0]->id, $this->testData[1]->id]);
    });

    it('can select all rows', function () {
        Livewire::test(DataTable::class)
            ->call('selectAll')
            ->assertCount('selectedRows', 3);
    });

    it('can deselect all rows', function () {
        Livewire::test(DataTable::class)
            ->call('selectAll')
            ->assertCount('selectedRows', 3)
            ->call('deselectAll')
            ->assertCount('selectedRows', 0);
    });

    it('can delete multiple selected rows', function () {
        $selectedIds = [$this->testData[0]->id, $this->testData[1]->id];
        
        Livewire::test(DataTable::class)
            ->set('selectedRows', $selectedIds)
            ->call('deleteSelected')
            ->assertHasNoErrors();
        
        expect(ImportedData::count())->toBe(1)
            ->and(ImportedData::whereIn('id', $selectedIds)->count())->toBe(0);
    });

    it('confirms before deleting selected rows', function () {
        Livewire::test(DataTable::class)
            ->set('selectedRows', [$this->testData[0]->id])
            ->call('confirmDeleteSelected')
            ->assertSet('showDeleteConfirmation', true);
    });
});

describe('Row Editing', function () {
    it('can edit a row', function () {
        $importedData = $this->testData[0];
        
        Livewire::test(DataTable::class)
            ->call('editRow', $importedData->id)
            ->assertSet('editingRow.id', $importedData->id)
            ->set('editData.name', 'Updated Name')
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

    it('validates edit data', function () {
        Livewire::test(DataTable::class)
            ->call('editRow', $this->testData[0]->id)
            ->set('editData.email', 'invalid-email')
            ->call('saveEdit')
            ->assertHasErrors(['editData.email']);
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

    it('can close row details modal', function () {
        Livewire::test(DataTable::class)
            ->call('viewRow', $this->testData[0]->id)
            ->assertSet('showModal', true)
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertSet('modalData', []);
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

describe('Pagination', function () {
    beforeEach(function () {
        // Créer plus de données pour tester la pagination
        for ($i = 4; $i <= 25; $i++) {
            createImportedData($this->importHistory, [
                'name' => "User {$i}",
                'email' => "user{$i}@test.com",
                'age' => 20 + $i
            ]);
        }
    });

    it('can change pagination per page', function () {
        Livewire::test(DataTable::class)
            ->set('perPage', 5)
            ->assertSee('User 4')
            ->assertDontSee('User 10'); // Should not see beyond page 1
    });

    it('can navigate between pages', function () {
        Livewire::test(DataTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSee('User 14'); // Should see items from page 2
    });

    it('maintains filters across pages', function () {
        Livewire::test(DataTable::class)
            ->set('perPage', 5)
            ->set('filters.name', 'User')
            ->call('gotoPage', 2)
            ->assertSee('User'); // Should still see filtered results
    });

    it('resets to page 1 when search changes', function () {
        $component = Livewire::test(DataTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->set('search', 'User 5');
        
        expect($component->get('page'))->toBe(1);
    });
});

describe('Export Functionality', function () {
    it('can export filtered data as CSV', function () {
        Livewire::test(DataTable::class)
            ->set('filters.city', 'Paris')
            ->call('exportData', 'csv')
            ->assertHasNoErrors();
        
        // Vérifier qu'un fichier a été généré
        expect(\Illuminate\Support\Facades\Storage::disk('public')->exists('exports/'))->toBeTrue();
    });

    it('can export all data when no filters applied', function () {
        Livewire::test(DataTable::class)
            ->call('exportData', 'xlsx')
            ->assertHasNoErrors();
    });

    it('includes only visible columns in export', function () {
        Livewire::test(DataTable::class)
            ->set('visibleColumns', ['name', 'email'])
            ->call('exportData', 'csv')
            ->assertHasNoErrors();
    });
});

describe('Real-time Updates', function () {
    it('refreshes data when new import occurs', function () {
        $component = Livewire::test(DataTable::class);
        
        // Simuler un nouvel import
        $newImportHistory = createImportHistory($this->workspace);
        createImportedData($newImportHistory, ['name' => 'New User', 'email' => 'new@test.com']);
        
        $component->call('refreshData')
            ->assertSee('New User');
    });

    it('updates statistics in real-time', function () {
        Livewire::test(DataTable::class)
            ->assertSee('3') // Initial count
            ->call('deleteRow', $this->testData[0]->id)
            ->assertSee('2'); // Updated count
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

    it('respects workspace permissions', function () {
        // Tester avec un utilisateur non autorisé
        $unauthorizedUser = User::factory()->create();
        
        $this->actingAs($unauthorizedUser);
        
        Livewire::test(DataTable::class)
            ->assertStatus(403); // Should be forbidden
    });
});
