<?php

namespace App\Livewire;

use App\Repositories\ImportHistoryRepository;
use App\Services\ImportService;
use App\Services\WorkspaceService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ImportHistory extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $showDetails = null;
    public $showDataModal = false;
    public $selectedImportData = [];
    public $selectedImport = null;
    public $showRollbackConfirm = false;
    public $importToRollback = null;

    protected ImportHistoryRepository $importHistoryRepository;
    protected ImportService $importService;
    protected WorkspaceService $workspaceService;

    public function boot(
        ImportHistoryRepository $importHistoryRepository,
        ImportService $importService,
        WorkspaceService $workspaceService
    ) {
        $this->importHistoryRepository = $importHistoryRepository;
        $this->importService = $importService;
        $this->workspaceService = $workspaceService;
    }

    public function viewDetails($importId)
    {
        $this->showDetails = $this->showDetails === $importId ? null : $importId;
    }

    public function viewImportData($importId)
    {
        $import = $this->importHistoryRepository->find($importId);
        
        if (!$import) {
            session()->flash('error', 'Import introuvable.');
            return;
        }

        // Récupérer les données de cet import spécifique
        $this->selectedImportData = $import->importedData()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        
        $this->selectedImport = $import;
        $this->showDataModal = true;
    }

    public function closeDataModal()
    {
        $this->showDataModal = false;
        $this->selectedImportData = [];
        $this->selectedImport = null;
    }

    public function confirmRollback($importId)
    {
        $import = $this->importHistoryRepository->find($importId);
        
        if (!$import || $import->status !== 'completed') {
            session()->flash('error', 'Impossible de faire un rollback sur cet import.');
            return;
        }

        $this->importToRollback = $import;
        $this->showRollbackConfirm = true;
    }

    public function rollbackImport()
    {
        if (!$this->importToRollback) {
            return;
        }

        try {
            $this->importService->rollbackImport($this->importToRollback);
            
            session()->flash('success', 'Import rollback effectué avec succès. ' . 
                $this->importToRollback->successful_rows . ' lignes ont été supprimées.');
            
            $this->closeRollbackConfirm();
            
            // Rafraîchir la liste
            $this->resetPage();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors du rollback: ' . $e->getMessage());
        }
    }

    public function closeRollbackConfirm()
    {
        $this->showRollbackConfirm = false;
        $this->importToRollback = null;
    }

    public function render()
    {
        $currentWorkspace = $this->workspaceService->getCurrentWorkspace(Auth::user());
        
        return view('livewire.import-history', [
            'imports' => $this->importHistoryRepository->paginateForWorkspace($currentWorkspace, $this->perPage),
            'statistics' => $this->importHistoryRepository->getStatisticsForWorkspace($currentWorkspace),
        ]);
    }
}
