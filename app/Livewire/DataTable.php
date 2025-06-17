<?php

namespace App\Livewire;

use App\Models\ImportedData;
use App\Models\Workspace;
use App\Repositories\ImportedDataRepository;
use App\Services\ExportService;
use App\Services\WorkspaceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class DataTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'id';
    public $sortDirection = 'desc';
    public $perPage = 15;
    public $selectedRows = [];
    public $selectAll = false;
    public $showModal = false;
    public $modalData = [];
    public $editingRow = null;
    public $editData = [];
    public $currentWorkspace = null;
    
    // Propriétés pour le filtrage unifié
    public $filterColumn = 'all'; // 'all' pour toutes les colonnes ou nom de colonne spécifique
    public $filterValue = '';
    public $availableColumns = [];

    protected ImportedDataRepository $importedDataRepository;
    protected ExportService $exportService;
    protected WorkspaceService $workspaceService;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
        'filterColumn' => ['except' => 'all'],
        'filterValue' => ['except' => ''],
    ];

    public function boot(
        ImportedDataRepository $importedDataRepository,
        ExportService $exportService,
        WorkspaceService $workspaceService
    ) {
        $this->importedDataRepository = $importedDataRepository;
        $this->exportService = $exportService;
        $this->workspaceService = $workspaceService;
    }

    public function mount($workspace = null)
    {
        
        // Si un workspace spécifique est passé, l'utiliser, sinon utiliser le workspace courant
        $this->currentWorkspace = $workspace ? Workspace::findOrFail($workspace) : $this->workspaceService->getCurrentWorkspace(Auth::user());

        // Vérifier les permissions d'accès au workspace
        if ($this->currentWorkspace && !$this->currentWorkspace->canUserAccess(Auth::user(), 'view')) {
            abort(403, 'Vous n\'avez pas accès à ce workspace.');
        }
        
        $this->loadAvailableColumns();
    }
    
    public function loadAvailableColumns()
    {
        if ($this->currentWorkspace) {
            $this->availableColumns = $this->importedDataRepository->getUniqueColumns($this->currentWorkspace);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterColumn()
    {
        $this->filterValue = '';
        $this->resetPage();
    }

    public function updatedFilterValue()
    {
        $this->resetPage();
    }
    
    public function clearFilter()
    {
        $this->filterColumn = 'all';
        $this->filterValue = '';
        $this->search = '';
        $this->resetPage();
    }

    public function sortBy($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }
    
    public function sortByColumn($column)
    {
        return $this->sortBy($column);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = $this->getData()->pluck('id')->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function deleteRow($id)
    {
        $row = $this->importedDataRepository->findById($id, $this->currentWorkspace);
        
        // Vérifier que la ligne existe et que l'utilisateur a les permissions
        if ($row && $this->currentWorkspace && 
            $this->currentWorkspace->canUserAccess(Auth::user(), 'edit')) {
            
            $this->importedDataRepository->delete($row);
            session()->flash('message', 'Ligne supprimée avec succès.');
        } else {
            session()->flash('error', 'Vous n\'avez pas les permissions pour supprimer cette ligne.');
        }
    }

    public function deleteSelected()
    {
        if (!$this->currentWorkspace || !$this->currentWorkspace->canUserAccess(Auth::user(), 'edit')) {
            session()->flash('error', 'Vous n\'avez pas les permissions pour supprimer ces lignes.');
            return;
        }

        $deletedCount = 0;
        foreach ($this->selectedRows as $id) {
            $row = $this->importedDataRepository->findById($id, $this->currentWorkspace);
            if ($row) {
                $this->importedDataRepository->delete($row);
                $deletedCount++;
            }
        }
        
        $this->selectedRows = [];
        $this->selectAll = false;
        session()->flash('message', "$deletedCount lignes supprimées avec succès.");
    }

    public function viewRow($id)
    {
        $row = $this->importedDataRepository->findById($id, $this->currentWorkspace);
        
        // Vérifier que la ligne existe dans le workspace courant
        if ($row && $this->currentWorkspace) {
            $this->modalData = $row->data;
            $this->showModal = true;
        } else {
            session()->flash('error', 'Ligne introuvable ou accès non autorisé.');
        }
    }

    public function editRow($id)
    {
        $row = $this->importedDataRepository->findById($id, $this->currentWorkspace);
        
        // Vérifier les permissions d'édition et l'existence dans le workspace
        if ($row && $this->currentWorkspace && 
            $this->currentWorkspace->canUserAccess(Auth::user(), 'edit')) {
            
            $this->editingRow = $row;
            $this->editData = $row->data;
        } else {
            session()->flash('error', 'Vous n\'avez pas les permissions pour éditer cette ligne.');
        }
    }

    public function saveEdit()
    {
        if ($this->editingRow && $this->currentWorkspace && 
            $this->currentWorkspace->canUserAccess(Auth::user(), 'edit')) {
            
            $this->importedDataRepository->update($this->editingRow, ['data' => $this->editData]);
            $this->editingRow = null;
            $this->editData = [];
            session()->flash('message', 'Ligne modifiée avec succès.');
        } else {
            session()->flash('error', 'Vous n\'avez pas les permissions pour sauvegarder cette modification.');
        }
    }

    public function cancelEdit()
    {
        $this->editingRow = null;
        $this->editData = [];
    }

    public function exportCsv()
    {
        try {
            // Préparer les filtres pour l'export (même logique que getData())
            $filters = [];
            $searchValue = null;
            
            // Si on filtre par une colonne spécifique
            if ($this->filterColumn !== 'all' && !empty($this->filterValue)) {
                $filters[$this->filterColumn] = $this->filterValue;
            }
            
            // Si on fait une recherche globale (toutes les colonnes)
            if ($this->filterColumn === 'all' && !empty($this->filterValue)) {
                $searchValue = $this->filterValue;
            }
            
            // Support pour l'ancienne recherche globale (pour compatibilité)
            if (!empty($this->search)) {
                $searchValue = $this->search;
            }
            
            $filename = $this->exportService->exportToCsv($filters, $this->currentWorkspace, $searchValue);
            
            // Redirection vers le téléchargement
            return $this->redirect(route('download.export', ['filename' => $filename]));
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        try {
            // Préparer les filtres pour l'export (même logique que getData())
            $filters = [];
            $searchValue = null;
            
            // Si on filtre par une colonne spécifique
            if ($this->filterColumn !== 'all' && !empty($this->filterValue)) {
                $filters[$this->filterColumn] = $this->filterValue;
            }
            
            // Si on fait une recherche globale (toutes les colonnes)
            if ($this->filterColumn === 'all' && !empty($this->filterValue)) {
                $searchValue = $this->filterValue;
            }
            
            // Support pour l'ancienne recherche globale (pour compatibilité)
            if (!empty($this->search)) {
                $searchValue = $this->search;
            }
            
            $filename = $this->exportService->exportToExcel($filters, $this->currentWorkspace, $searchValue);
            
            // Redirection vers le téléchargement
            return $this->redirect(route('download.export', ['filename' => $filename]));
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    public function exportJson()
    {
        try {
            // Préparer les filtres pour l'export (même logique que getData())
            $filters = [];
            $searchValue = null;
            
            // Si on filtre par une colonne spécifique
            if ($this->filterColumn !== 'all' && !empty($this->filterValue)) {
                $filters[$this->filterColumn] = $this->filterValue;
            }
            
            // Si on fait une recherche globale (toutes les colonnes)
            if ($this->filterColumn === 'all' && !empty($this->filterValue)) {
                $searchValue = $this->filterValue;
            }
            
            // Support pour l'ancienne recherche globale (pour compatibilité)
            if (!empty($this->search)) {
                $searchValue = $this->search;
            }
            
            $filename = $this->exportService->exportToJson($filters, $this->currentWorkspace, $searchValue);
            
            // Redirection vers le téléchargement
            return $this->redirect(route('download.export', ['filename' => $filename]));
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    #[On('file-imported')]
    public function refreshData()
    {
        // Rafraîchir les données après un import
        $this->loadAvailableColumns();
        $this->resetPage();
    }

    /**
     * Changer de workspace en cours d'utilisation
     */
    public function switchWorkspace(Workspace $workspace)
    {
        // Vérifier les permissions d'accès au nouveau workspace
        if (!$workspace->canUserAccess(Auth::user(), 'view')) {
            session()->flash('error', 'Vous n\'avez pas accès à ce workspace.');
            return;
        }

        $this->currentWorkspace = $workspace;
        $this->workspaceService->setCurrentWorkspace(Auth::user(), $workspace);
        
        // Réinitialiser les filtres et la pagination
        $this->reset(['search', 'filterColumn', 'filterValue', 'selectedRows', 'selectAll', 'editingRow']);
        $this->resetPage();
        
        // Recharger les colonnes disponibles pour le nouveau workspace
        $this->loadAvailableColumns();
        
        session()->flash('message', "Workspace '{$workspace->name}' sélectionné.");
    }

    public function getData()
    {
        // S'assurer que nous avons un workspace courant
        if (!$this->currentWorkspace) {
            return collect();
        }

        // Préparation des filtres
        $filters = [];
        $searchValue = null;
        
        // Si on filtre par une colonne spécifique
        if ($this->filterColumn !== 'all' && !empty($this->filterValue)) {
            $filters[$this->filterColumn] = $this->filterValue;
        }
        
        // Si on fait une recherche globale (toutes les colonnes)
        if ($this->filterColumn === 'all' && !empty($this->filterValue)) {
            $searchValue = $this->filterValue;
        }
        
        // Support pour l'ancienne recherche globale (pour compatibilité)
        if (!empty($this->search)) {
            $searchValue = $this->search;
        }
        
        return $this->importedDataRepository->paginate(
            $this->perPage,
            $searchValue,
            $this->sortBy,
            $this->sortDirection,
            $filters,
            $this->currentWorkspace
        );
    }

    /**
     * Obtenir des statistiques sur le workspace courant
     */
    public function getWorkspaceStats()
    {
        if (!$this->currentWorkspace) {
            return [
                'total_rows' => 0,
                'total_imports' => 0,
                'total_columns' => 0,
            ];
        }

        return [
            'total_rows' => $this->importedDataRepository->count($this->currentWorkspace),
            'total_imports' => $this->currentWorkspace->importHistories()->count(),
            'total_columns' => count($this->availableColumns),
        ];
    }

    /**
     * Vérifier si l'utilisateur peut effectuer une action sur le workspace courant
     */
    public function canPerformAction(string $action): bool
    {
        if (!$this->currentWorkspace) {
            return false;
        }

        return $this->currentWorkspace->canUserAccess(Auth::user(), $action);
    }

    /**
     * Événement déclenché quand un workspace est changé depuis un autre composant
     */
    #[On('workspace-changed')]
    public function onWorkspaceChanged($workspaceId)
    {
        $workspace = $this->workspaceService->findById($workspaceId);
        if ($workspace) {
            $this->switchWorkspace($workspace);
        }
    }

    public function getColumns()
    {
        return $this->importedDataRepository->getUniqueColumns($this->currentWorkspace);
    }

    public function render()
    {
        return view('livewire.data-table', [
            'data' => $this->getData(),
            'columns' => $this->getColumns(),
            'availableColumns' => $this->availableColumns,
        ]);
    }
}
