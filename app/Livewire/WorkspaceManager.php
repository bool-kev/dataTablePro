<?php

namespace App\Livewire;

use App\Services\WorkspaceService;
use App\Repositories\WorkspaceRepository;
use Livewire\Component;
use Livewire\WithPagination;

class WorkspaceManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showCreateModal = false;
    public $selectedWorkspace = null;
    public $showDeleteModal = false;
    public $showSettingsModal = false;

    // Propriétés pour l'édition
    public $editingName = '';
    public $editingDescription = '';

    // Propriétés pour l'invitation d'utilisateurs
    public $inviteEmail = '';
    public $inviteRole = 'viewer';

    protected WorkspaceService $workspaceService;
    protected WorkspaceRepository $workspaceRepository;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function boot(WorkspaceService $workspaceService, WorkspaceRepository $workspaceRepository)
    {
        $this->workspaceService = $workspaceService;
        $this->workspaceRepository = $workspaceRepository;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function openSettings($workspaceId)
    {
        $this->selectedWorkspace = $this->workspaceRepository->findById($workspaceId);
        
        if ($this->selectedWorkspace) {
            $this->editingName = $this->selectedWorkspace->name;
            $this->editingDescription = $this->selectedWorkspace->description ?? '';
            $this->showSettingsModal = true;
        }
    }

    public function closeSettingsModal()
    {
        $this->showSettingsModal = false;
        $this->selectedWorkspace = null;
        $this->reset(['editingName', 'editingDescription']);
    }

    public function updateWorkspace()
    {
        $this->validate([
            'editingName' => 'required|string|min:3|max:255',
            'editingDescription' => 'nullable|string|max:500',
        ]);

        if ($this->selectedWorkspace) {
            $this->workspaceRepository->update($this->selectedWorkspace, [
                'name' => $this->editingName,
                'description' => $this->editingDescription,
            ]);
            
            session()->flash('success', 'Workspace mis à jour avec succès.');
            $this->closeSettingsModal();
        }
    }

    public function confirmDelete($workspaceId)
    {
        $this->selectedWorkspace = $this->workspaceRepository->findById($workspaceId);
        $this->showDeleteModal = true;
    }

    public function deleteWorkspace()
    {
        if ($this->selectedWorkspace) {
            // Vérifier que l'utilisateur est propriétaire
            if ($this->selectedWorkspace->owner_id !== auth()->id()) {
                session()->flash('error', 'Seul le propriétaire peut supprimer un workspace.');
                return;
            }
            
            $this->workspaceService->deleteWorkspace($this->selectedWorkspace);
            
            session()->flash('success', 'Workspace supprimé avec succès.');
            $this->showDeleteModal = false;
            $this->selectedWorkspace = null;
        }
    }

    public function inviteUser()
    {
        $this->validate([
            'inviteEmail' => 'required|email',
            'inviteRole' => 'required|in:viewer,editor,admin',
        ]);

        if ($this->selectedWorkspace) {
            if ($this->workspaceService->inviteUserToWorkspace(
                $this->selectedWorkspace, 
                $this->inviteEmail, 
                $this->inviteRole
            )) {
                session()->flash('success', 'Invitation envoyée avec succès.');
                $this->reset(['inviteEmail', 'inviteRole']);
            } else {
                session()->flash('error', 'Impossible d\'inviter cet utilisateur.');
            }
        }
    }

    public function switchToWorkspace($workspaceId)
    {
        $workspace = $this->workspaceRepository->findById($workspaceId);
        
        if ($workspace && $this->workspaceService->switchWorkspace(auth()->user(), $workspace)) {
            return $this->redirect(route('dashboard'));
        }
        
        session()->flash('error', 'Impossible de basculer vers ce workspace.');
    }

    public function getWorkspaces()
    {
        if ($this->search) {
            return $this->workspaceRepository->searchWorkspaces(auth()->user(), $this->search);
        }
        
        return $this->workspaceRepository->paginate(auth()->user(), 12);
    }

    public function render()
    {
        return view('livewire.workspace-manager', [
            'workspaces' => $this->getWorkspaces(),
        ]);
    }
}
