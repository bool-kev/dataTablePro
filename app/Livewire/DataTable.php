<?php

namespace App\Livewire;

use App\Models\ImportedData;
use App\Models\Workspace;
use App\Repositories\ImportedDataRepository;
use App\Services\ExportService;
use App\Services\WorkspaceService;
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
    public $filters = [];
    public $selectedRows = [];
    public $selectAll = false;
    public $showModal = false;
    public $modalData = [];
    public $editingRow = null;
    public $editData = [];
    public $currentWorkspace = null;

    protected ImportedDataRepository $importedDataRepository;
    protected ExportService $exportService;
    protected WorkspaceService $workspaceService;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
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

    public function mount()
    {
        $this->filters = [];
        $this->currentWorkspace = $this->workspaceService->getCurrentWorkspace(auth()->user());
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilters()
    {
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
        $row = $this->importedDataRepository->findById($id);
        
        if ($row) {
            $this->importedDataRepository->delete($row);
            session()->flash('message', 'Ligne supprimée avec succès.');
        }
    }

    public function deleteSelected()
    {
        foreach ($this->selectedRows as $id) {
            $row = $this->importedDataRepository->findById($id);
            if ($row) {
                $this->importedDataRepository->delete($row);
            }
        }
        
        $this->selectedRows = [];
        $this->selectAll = false;
        session()->flash('message', count($this->selectedRows) . ' lignes supprimées avec succès.');
    }

    public function viewRow($id)
    {
        $row = $this->importedDataRepository->findById($id);
        
        if ($row) {
            $this->modalData = $row->data;
            $this->showModal = true;
        }
    }

    public function editRow($id)
    {
        $row = $this->importedDataRepository->findById($id);
        
        if ($row) {
            $this->editingRow = $row;
            $this->editData = $row->data;
        }
    }

    public function saveEdit()
    {
        if ($this->editingRow) {
            $this->importedDataRepository->update($this->editingRow, ['data' => $this->editData]);
            $this->editingRow = null;
            $this->editData = [];
            session()->flash('message', 'Ligne modifiée avec succès.');
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
            $filename = $this->exportService->exportToCsv($this->filters, $this->currentWorkspace);
            session()->flash('message', 'Export CSV créé: ' . $filename);
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        try {
            $filename = $this->exportService->exportToExcel($this->filters, $this->currentWorkspace);
            session()->flash('message', 'Export Excel créé: ' . $filename);
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    #[On('file-imported')]
    public function refreshData()
    {
        // Rafraîchir les données après un import
        $this->resetPage();
    }

    public function getData()
    {
        return $this->importedDataRepository->paginate(
            $this->perPage,
            $this->search,
            $this->sortBy,
            $this->sortDirection,
            $this->filters,
            $this->currentWorkspace
        );
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
        ]);
    }
}
