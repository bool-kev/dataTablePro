<?php

namespace App\Livewire;

use App\Services\ImportService;
use App\Services\WorkspaceService;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileUpload extends Component
{
    use WithFileUploads;

    public $file;
    public $uploading = false;
    public $progress = 0;
    public $currentWorkspace = null;
    public $showStatsModal = false;
    public $importStats = null;

    protected ImportService $importService;
    protected WorkspaceService $workspaceService;

    protected $rules = [
        'file' => 'required|file|mimes:csv,xlsx,xls,tsv|max:10240', // 10MB max
    ];

    protected $messages = [
        'file.required' => 'Veuillez sélectionner un fichier.',
        'file.mimes' => 'Le fichier doit être au format CSV, XLSX, XLS ou TSV.',
        'file.max' => 'Le fichier ne doit pas dépasser 10 MB.',
    ];

    public function boot(ImportService $importService, WorkspaceService $workspaceService)
    {
        $this->importService = $importService;
        $this->workspaceService = $workspaceService;
    }

    public function mount()
    {
        $this->currentWorkspace = $this->workspaceService->getCurrentWorkspace(auth()->user());
        
        if (!$this->currentWorkspace) {
            session()->flash('error', 'Aucun workspace sélectionné. Veuillez créer ou sélectionner un workspace.');
        }
    }

    public function updatedFile()
    {
        $this->validate();
        $this->upload();
    }

    public function upload()
    {
        if (!$this->currentWorkspace) {
            session()->flash('error', 'Aucun workspace sélectionné.');
            return;
        }


        $this->uploading = true;
        $this->progress = 0;

        try {
            // Simuler le progrès
            for ($i = 20; $i <= 80; $i += 20) {
                $this->progress = $i;
                usleep(200000); // 0.2 secondes
            }

            $importHistory = $this->importService->processFile($this->file, $this->currentWorkspace);
            
            $this->progress = 100;
            
            // Préparer les statistiques pour le modal
            $this->importStats = [
                'filename' => $this->file->getClientOriginalName(),
                'filesize' => $this->file->getSize(),
                'total_rows' => $importHistory->total_rows,
                'successful_rows' => $importHistory->successful_rows,
                'failed_rows' => $importHistory->failed_rows,
                'success_rate' => $importHistory->total_rows > 0 ? round(($importHistory->successful_rows / $importHistory->total_rows) * 100, 2) : 0,
                'errors' => $importHistory->error_details ? json_decode($importHistory->error_details, true) : [],
                'import_date' => $importHistory->created_at->format('d/m/Y H:i:s'),
                'workspace_name' => $this->currentWorkspace->name
            ];
            // Émettre un événement pour rafraîchir la table
            $this->dispatch('file-imported');
            
            // Reset le formulaire mais garder les stats
            $this->reset(['file', 'uploading', 'progress']);
            
            // Afficher le modal avec les statistiques
            $this->showStatsModal = true;
            

        } catch (\Exception $e) {
            $this->progress = 0;
            session()->flash('error', 'Erreur lors de l\'import: ' . $e->getMessage());
        } finally {
            $this->uploading = false;
        }
    }

    public function closeStatsModal()
    {
        $this->showStatsModal = false;
        $this->importStats = null;
    }

    public function viewData()
    {
        $this->closeStatsModal();
        return redirect()->route('data-table');
    }

    public function render()
    {
        return view('livewire.file-upload');
    }
}
