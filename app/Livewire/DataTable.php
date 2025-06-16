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

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->filters = [];
        $workspaceService = app(WorkspaceService::class);
        $this->currentWorkspace = $workspaceService->getCurrentWorkspace(auth()->user());
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
        $repository = app(ImportedDataRepository::class);
        $row = $repository->findById($id);
        
        if ($row) {
            $repository->delete($row);
            session()->flash('message', 'Ligne supprimée avec succès.');
        }
    }

    public function deleteSelected()
    {
        $repository = app(ImportedDataRepository::class);
        
        foreach ($this->selectedRows as $id) {
            $row = $repository->findById($id);
            if ($row) {
                $repository->delete($row);
            }
        }
        
        $this->selectedRows = [];
        $this->selectAll = false;
        session()->flash('message', count($this->selectedRows) . ' lignes supprimées avec succès.');
    }

    public function viewRow($id)
    {
        $repository = app(ImportedDataRepository::class);
        $row = $repository->findById($id);
        
        if ($row) {
            $this->modalData = $row->data;
            $this->showModal = true;
        }
    }

    public function editRow($id)
    {
        $repository = app(ImportedDataRepository::class);
        $row = $repository->findById($id);
        
        if ($row) {
            $this->editingRow = $row;
            $this->editData = $row->data;
        }
    }

    public function saveEdit()
    {
        $repository = app(ImportedDataRepository::class);
        
        if ($this->editingRow) {
            $repository->update($this->editingRow, ['data' => $this->editData]);
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
        $exportService = app(ExportService::class);
        
        try {
            $filename = $exportService->exportToCsv($this->filters, $this->currentWorkspace);
            session()->flash('message', 'Export CSV créé: ' . $filename);
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        $exportService = app(ExportService::class);
        
        try {
            $filename = $exportService->exportToExcel($this->filters, $this->currentWorkspace);
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
        $repository = app(ImportedDataRepository::class);
        return $repository->paginate(
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
        $repository = app(ImportedDataRepository::class);
        return $repository->getUniqueColumns($this->currentWorkspace);
    }

    public function render()
    {
        return view('livewire.data-table', [
            'data' => $this->getData(),
            'columns' => $this->getColumns(),
        ]);
    }
}
