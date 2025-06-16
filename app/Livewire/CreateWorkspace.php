<?php

namespace App\Livewire;

use App\Services\WorkspaceService;
use Livewire\Component;

class CreateWorkspace extends Component
{
    public $name = '';
    public $description = '';
    public $database_type = 'sqlite';
    public $isCreating = false;

    protected WorkspaceService $workspaceService;

    protected $rules = [
        'name' => 'required|string|min:3|max:255',
        'description' => 'nullable|string|max:500',
        'database_type' => 'required|in:sqlite,mysql,postgresql',
    ];

    protected $messages = [
        'name.required' => 'Le nom du workspace est obligatoire.',
        'name.min' => 'Le nom doit contenir au moins 3 caractères.',
        'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
        'description.max' => 'La description ne peut pas dépasser 500 caractères.',
        'database_type.required' => 'Le type de base de données est obligatoire.',
        'database_type.in' => 'Type de base de données non supporté.',
    ];

    public function boot(WorkspaceService $workspaceService)
    {
        $this->workspaceService = $workspaceService;
    }

    public function createWorkspace()
    {
        $this->validate();
        
        $this->isCreating = true;
        
        try {
            $workspace = $this->workspaceService->createWorkspace(auth()->user(), [
                'name' => $this->name,
                'description' => $this->description,
                'database_type' => $this->database_type,
            ]);
            
            // Basculer automatiquement vers le nouveau workspace
            $this->workspaceService->switchWorkspace(auth()->user(), $workspace);
            
            session()->flash('success', 'Workspace "' . $workspace->name . '" créé avec succès !');
            
            // Rediriger vers le dashboard
            return $this->redirect(route('dashboard'));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la création du workspace: ' . $e->getMessage());
        } finally {
            $this->isCreating = false;
        }
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'database_type']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.create-workspace');
    }
}
