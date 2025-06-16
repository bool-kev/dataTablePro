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

    protected $queryString = [
        'search' => ['except' => ''],
    ];

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
        $workspaceRepository = app(WorkspaceRepository::class);
        $this->selectedWorkspace = $workspaceRepository->findById($workspaceId);
        
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
            $workspaceRepository = app(WorkspaceRepository::class);
            
            $workspaceRepository->update($this->selectedWorkspace, [
                'name' => $this->editingName,
                'description' => $this->editingDescription,
            ]);
            
            session()->flash('success', 'Workspace mis à jour avec succès.');
            $this->closeSettingsModal();
        }
    }

    public function confirmDelete($workspaceId)
    {
        $workspaceRepository = app(WorkspaceRepository::class);
        $this->selectedWorkspace = $workspaceRepository->findById($workspaceId);
        $this->showDeleteModal = true;
    }

    public function deleteWorkspace()
    {
        if ($this->selectedWorkspace) {
            $workspaceService = app(WorkspaceService::class);
            
            // Vérifier que l'utilisateur est propriétaire
            if ($this->selectedWorkspace->owner_id !== auth()->id()) {
                session()->flash('error', 'Seul le propriétaire peut supprimer un workspace.');
                return;
            }
            
            $workspaceService->deleteWorkspace($this->selectedWorkspace);
            
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
            $workspaceService = app(WorkspaceService::class);
            
            if ($workspaceService->inviteUserToWorkspace(
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
        $workspaceService = app(WorkspaceService::class);
        $workspaceRepository = app(WorkspaceRepository::class);
        
        $workspace = $workspaceRepository->findById($workspaceId);
        
        if ($workspace && $workspaceService->switchWorkspace(auth()->user(), $workspace)) {
            return $this->redirect(route('dashboard'));
        }
        
        session()->flash('error', 'Impossible de basculer vers ce workspace.');
    }

    public function getWorkspaces()
    {
        $workspaceRepository = app(WorkspaceRepository::class);
        
        if ($this->search) {
            return $workspaceRepository->searchWorkspaces(auth()->user(), $this->search);
        }
        
        return $workspaceRepository->paginate(auth()->user(), 12);
    }

    public function render()
    {
        return view('livewire.workspace-manager', [
            'workspaces' => $this->getWorkspaces(),
        ]);
    }
}
