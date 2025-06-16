<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\ImportHistory;
use App\Models\ImportedData;
use App\Services\WorkspaceService;
use App\Repositories\ImportedDataRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user1 = User::factory()->create(['email' => 'user1@example.com']);
    $this->user2 = User::factory()->create(['email' => 'user2@example.com']);
    
    $this->workspaceService = app(WorkspaceService::class);
    $this->importedDataRepository = app(ImportedDataRepository::class);
});

describe('Workspace Data Isolation', function () {
    it('should create workspaces with isolated data', function () {
        // Créer deux workspaces différents
        $workspace1 = $this->workspaceService->createWorkspace($this->user1, [
            'name' => 'Workspace 1',
            'description' => 'Premier workspace de test'
        ]);
        
        $workspace2 = $this->workspaceService->createWorkspace($this->user2, [
            'name' => 'Workspace 2', 
            'description' => 'Deuxième workspace de test'
        ]);
        
        // Vérifier que les workspaces ont des IDs différents
        expect($workspace1->id)->not->toBe($workspace2->id);
        expect($workspace1->name)->toBe('Workspace 1');
        expect($workspace2->name)->toBe('Workspace 2');
    });

    it('should isolate data between workspaces', function () {
        // Créer deux workspaces
        $workspace1 = $this->workspaceService->createWorkspace($this->user1, [
            'name' => 'Workspace 1'
        ]);
        
        $workspace2 = $this->workspaceService->createWorkspace($this->user2, [
            'name' => 'Workspace 2'
        ]);
        
        // Ajouter des données au workspace 1
        $importHistory1 = ImportHistory::create([
            'workspace_id' => $workspace1->id,
            'filename' => 'test1.csv',
            'original_filename' => 'test1.csv',
            'file_path' => '/tmp/test1.csv',
            'file_type' => 'csv',
            'status' => 'completed'
        ]);
        
        $data1 = ImportedData::create([
            'import_history_id' => $importHistory1->id,
            'data' => ['name' => 'John', 'age' => 30]
        ]);
        
        // Ajouter des données au workspace 2
        $importHistory2 = ImportHistory::create([
            'workspace_id' => $workspace2->id,
            'filename' => 'test2.csv',
            'original_filename' => 'test2.csv', 
            'file_path' => '/tmp/test2.csv',
            'file_type' => 'csv',
            'status' => 'completed'
        ]);
        
        $data2 = ImportedData::create([
            'import_history_id' => $importHistory2->id,
            'data' => ['name' => 'Jane', 'age' => 25]
        ]);
        
        // Vérifier l'isolation : workspace 1 ne voit que ses données
        $workspace1Data = $this->importedDataRepository->count($workspace1);
        $workspace2Data = $this->importedDataRepository->count($workspace2);
        
        expect($workspace1Data)->toBe(1);
        expect($workspace2Data)->toBe(1);
        
        // Vérifier que les données sont différentes
        $data1Retrieved = $this->importedDataRepository->findById($data1->id, $workspace1);
        $data2Retrieved = $this->importedDataRepository->findById($data2->id, $workspace2);
        
        expect($data1Retrieved)->not->toBeNull();
        expect($data2Retrieved)->not->toBeNull();
        expect($data1Retrieved->data['name'])->toBe('John');
        expect($data2Retrieved->data['name'])->toBe('Jane');
        
        // Vérifier qu'on ne peut pas accéder aux données de l'autre workspace
        $crossAccessData1 = $this->importedDataRepository->findById($data1->id, $workspace2);
        $crossAccessData2 = $this->importedDataRepository->findById($data2->id, $workspace1);
        
        expect($crossAccessData1)->toBeNull();
        expect($crossAccessData2)->toBeNull();
    });

    it('should prevent unauthorized access to workspace data', function () {
        // Créer un workspace pour user1
        $workspace = $this->workspaceService->createWorkspace($this->user1, [
            'name' => 'Private Workspace'
        ]);
        
        // Vérifier que user1 a accès
        expect($workspace->canUserAccess($this->user1, 'view'))->toBeTrue();
        expect($workspace->canUserAccess($this->user1, 'edit'))->toBeTrue();
        
        // Vérifier que user2 n'a pas accès
        expect($workspace->canUserAccess($this->user2, 'view'))->toBeFalse();
        expect($workspace->canUserAccess($this->user2, 'edit'))->toBeFalse();
        
        // Ajouter user2 avec des permissions limitées
        $workspace->users()->attach($this->user2, ['role' => 'viewer']);
        
        // Vérifier les permissions mises à jour
        expect($workspace->canUserAccess($this->user2, 'view'))->toBeTrue();
        expect($workspace->canUserAccess($this->user2, 'edit'))->toBeFalse();
    });

    it('should switch between workspaces correctly', function () {
        // Créer deux workspaces pour le même utilisateur
        $workspace1 = $this->workspaceService->createWorkspace($this->user1, [
            'name' => 'Workspace 1'
        ]);
        
        $workspace2 = $this->workspaceService->createWorkspace($this->user1, [
            'name' => 'Workspace 2'
        ]);
        
        // Basculer vers le workspace 1
        $switched1 = $this->workspaceService->switchWorkspace($this->user1, $workspace1);
        expect($switched1)->toBeTrue();
        expect(session('current_workspace_id'))->toBe($workspace1->id);
        
        // Basculer vers le workspace 2
        $switched2 = $this->workspaceService->switchWorkspace($this->user1, $workspace2);
        expect($switched2)->toBeTrue();
        expect(session('current_workspace_id'))->toBe($workspace2->id);
        
        // Essayer de basculer vers un workspace non autorisé
        $workspace3 = $this->workspaceService->createWorkspace($this->user2, [
            'name' => 'Unauthorized Workspace'
        ]);
        
        $switched3 = $this->workspaceService->switchWorkspace($this->user1, $workspace3);
        expect($switched3)->toBeFalse();
        expect(session('current_workspace_id'))->toBe($workspace2->id); // Reste inchangé
    });

    it('should clean up workspace data on deletion', function () {
        // Créer un workspace avec des données
        $workspace = $this->workspaceService->createWorkspace($this->user1, [
            'name' => 'Temporary Workspace'
        ]);
        
        // Ajouter des données d'import
        $importHistory = ImportHistory::create([
            'workspace_id' => $workspace->id,
            'filename' => 'temp.csv',
            'original_filename' => 'temp.csv',
            'file_path' => '/tmp/temp.csv',
            'file_type' => 'csv',
            'status' => 'completed'
        ]);
        
        // Ajouter des données importées
        $importedData = ImportedData::create([
            'import_history_id' => $importHistory->id,
            'data' => ['test' => 'data']
        ]);
        
        // Vérifier que les données existent avant suppression
        expect(ImportHistory::where('workspace_id', $workspace->id)->count())->toBe(1);
        expect(ImportedData::where('import_history_id', $importHistory->id)->count())->toBe(1);
        
        // Supprimer le workspace
        $deleted = $this->workspaceService->deleteWorkspace($workspace);
        expect($deleted)->toBeTrue();
        
        // Vérifier que les données du workspace ont été supprimées par cascade
        expect(ImportHistory::where('workspace_id', $workspace->id)->count())->toBe(0);
        expect(ImportedData::where('import_history_id', $importHistory->id)->count())->toBe(0);
        
        // Vérifier que le workspace n'existe plus
        expect(Workspace::find($workspace->id))->toBeNull();
    });
});
