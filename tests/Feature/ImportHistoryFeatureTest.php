<?php

/*
 * Script de test pour les nouvelles fonctionnalités d'historique des imports
 * 
 * Teste :
 * 1. Visualisation des données d'un import spécifique
 * 2. Rollback d'un import
 * 3. Popup de statistiques après upload
 */

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Workspace;
use App\Models\ImportHistory;
use App\Models\ImportedData;
use App\Services\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

class ImportHistoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Workspace $workspace;
    private ImportHistory $importHistory;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create(['user_id' => $this->user->id]);
        
        // Créer un import history avec des données
        $this->importHistory = ImportHistory::factory()->create([
            'workspace_id' => $this->workspace->id,
            'status' => 'completed',
            'successful_rows' => 5,
            'failed_rows' => 0,
            'total_rows' => 5
        ]);
        
        // Créer quelques données importées
        ImportedData::factory()->count(5)->create([
            'import_history_id' => $this->importHistory->id
        ]);
    }

    /** @test */
    public function it_can_view_import_specific_data()
    {
        $this->actingAs($this->user);
        
        Livewire::test('import-history')
            ->call('viewImportData', $this->importHistory->id)
            ->assertSet('showDataModal', true)
            ->assertSet('selectedImport.id', $this->importHistory->id)
            ->assertCount('selectedImportData', 5);
    }

    /** @test */
    public function it_can_rollback_an_import()
    {
        $this->actingAs($this->user);
        
        // Vérifier que les données existent
        $this->assertEquals(5, ImportedData::where('import_history_id', $this->importHistory->id)->count());
        
        Livewire::test('import-history')
            ->call('confirmRollback', $this->importHistory->id)
            ->assertSet('showRollbackConfirm', true)
            ->assertSet('importToRollback.id', $this->importHistory->id)
            ->call('rollbackImport')
            ->assertSet('showRollbackConfirm', false);
        
        // Vérifier que les données ont été supprimées
        $this->assertEquals(0, ImportedData::where('import_history_id', $this->importHistory->id)->count());
        
        // Vérifier que le statut a été mis à jour
        $this->importHistory->refresh();
        $this->assertEquals('rolled_back', $this->importHistory->status);
    }

    /** @test */
    public function it_shows_import_stats_popup_after_upload()
    {
        $this->actingAs($this->user);
        
        // Créer un fichier CSV de test
        $csvContent = "name,email\nJohn Doe,john@example.com\nJane Doe,jane@example.com";
        $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);
        
        Livewire::test('file-upload')
            ->set('file', $file)
            ->call('upload')
            ->assertSet('showStatsModal', true)
            ->assertNotNull('importStats');
    }

    /** @test */
    public function it_can_close_stats_modal()
    {
        $this->actingAs($this->user);
        
        Livewire::test('file-upload')
            ->set('showStatsModal', true)
            ->set('importStats', ['total_rows' => 5])
            ->call('closeStatsModal')
            ->assertSet('showStatsModal', false)
            ->assertNull('importStats');
    }

    /** @test */
    public function it_can_navigate_to_data_table_from_stats_modal()
    {
        $this->actingAs($this->user);
        
        Livewire::test('file-upload')
            ->set('showStatsModal', true)
            ->set('importStats', ['total_rows' => 5])
            ->call('viewData')
            ->assertRedirect(route('data-table'));
    }

    /** @test */
    public function it_prevents_rollback_of_non_completed_imports()
    {
        $this->actingAs($this->user);
        
        // Créer un import en cours ou échoué
        $failedImport = ImportHistory::factory()->create([
            'workspace_id' => $this->workspace->id,
            'status' => 'failed'
        ]);
        
        Livewire::test('import-history')
            ->call('confirmRollback', $failedImport->id)
            ->assertHasErrors() // Ou assertSessionHas('error')
            ->assertSet('showRollbackConfirm', false);
    }
}
