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

    protected ImportService $importService;
    protected WorkspaceService $workspaceService;

    protected $rules = [
        'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
    ];

    protected $messages = [
        'file.required' => 'Veuillez sélectionner un fichier.',
        'file.mimes' => 'Le fichier doit être au format CSV, XLSX ou XLS.',
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

        $this->validate();

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
            
            session()->flash('success', 
                "Fichier importé avec succès! " . 
                $importHistory->successful_rows . " lignes importées, " . 
                $importHistory->failed_rows . " erreurs."
            );

            // Émettre un événement pour rafraîchir la table
            $this->dispatch('file-imported');
            
            // Reset
            $this->reset(['file', 'uploading', 'progress']);

        } catch (\Exception $e) {
            $this->progress = 0;
            session()->flash('error', 'Erreur lors de l\'import: ' . $e->getMessage());
        } finally {
            $this->uploading = false;
        }
    }

    public function render()
    {
        return view('livewire.file-upload');
    }
}
