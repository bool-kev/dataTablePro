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

    protected WorkspaceService $workspaceService;
    protected WorkspaceRepository $workspaceRepository;

    public function boot(WorkspaceService $workspaceService, WorkspaceRepository $workspaceRepository)
    {
        $this->workspaceService = $workspaceService;
        $this->workspaceRepository = $workspaceRepository;
    }

    public function mount()
    {
        $this->loadWorkspaces();
    }

    public function loadWorkspaces()
    {
        $this->currentWorkspace = $this->workspaceService->getCurrentWorkspace(auth()->user());
        $this->availableWorkspaces = $this->workspaceRepository->getUserWorkspaces(auth()->user());
    }

    public function switchWorkspace($workspaceId)
    {
        $workspace = $this->workspaceRepository->findById($workspaceId);
        
        if ($workspace && $this->workspaceService->switchWorkspace(auth()->user(), $workspace)) {
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
