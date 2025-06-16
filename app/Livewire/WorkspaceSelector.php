<?php

namespace App\Livewire;

use App\Services\WorkspaceService;
use App\Repositories\WorkspaceRepository;
use Livewire\Component;

class WorkspaceSelector extends Component
{
    public $currentWorkspace;
    public $availableWorkspaces;
    public $showDropdown = false;

    public function mount()
    {
        $this->loadWorkspaces();
    }

    public function loadWorkspaces()
    {
        $workspaceService = app(WorkspaceService::class);
        $workspaceRepository = app(WorkspaceRepository::class);
        
        $this->currentWorkspace = $workspaceService->getCurrentWorkspace(auth()->user());
        $this->availableWorkspaces = $workspaceRepository->getUserWorkspaces(auth()->user());
    }

    public function switchWorkspace($workspaceId)
    {
        $workspaceService = app(WorkspaceService::class);
        $workspaceRepository = app(WorkspaceRepository::class);
        
        $workspace = $workspaceRepository->findById($workspaceId);
        
        if ($workspace && $workspaceService->switchWorkspace(auth()->user(), $workspace)) {
            $this->currentWorkspace = $workspace;
            $this->showDropdown = false;
            
            // Rediriger vers le dashboard du nouveau workspace
            return $this->redirect(route('dashboard'));
        }
        
        session()->flash('error', 'Impossible de basculer vers ce workspace.');
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function render()
    {
        return view('livewire.workspace-selector');
    }
}
