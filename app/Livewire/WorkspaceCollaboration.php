<?php

namespace App\Livewire;

use App\Models\Workspace;
use App\Services\WorkspaceInvitationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class WorkspaceCollaboration extends Component
{
    use WithPagination;

    public Workspace $workspace;
    
    // Form properties for inviting users
    public string $email = '';
    public string $role = 'viewer';
    public bool $showInviteForm = false;
    
    // Modal states
    public bool $showConfirmRemove = false;
    public $userToRemove = null;
    
    protected $rules = [
        'email' => 'required|email',
        'role' => 'required|in:viewer,editor,admin',
    ];

    protected $messages = [
        'email.required' => 'Email address is required.',
        'email.email' => 'Please enter a valid email address.',
        'role.required' => 'Please select a role.',
        'role.in' => 'Invalid role selected.',
    ];

    public function mount(Workspace $workspace)
    {
        $this->workspace = $workspace;
        
        // Check if user can manage this workspace
        if (!$this->workspace->canUserAccess(Auth::user(), 'admin')) {
            abort(403, 'You do not have permission to manage this workspace.');
        }
    }

    public function inviteUser(WorkspaceInvitationService $invitationService)
    {
        $this->validate();

        try {
            $invitationService->inviteUser(
                $this->workspace,
                $this->email,
                $this->role,
                Auth::user()
            );

            $this->reset(['email', 'role', 'showInviteForm']);
            $this->role = 'viewer'; // Reset to default
            
            session()->flash('success', 'Invitation sent successfully!');
            
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function removeUser($userId, WorkspaceInvitationService $invitationService)
    {
        try {
            $user = \App\Models\User::findOrFail($userId);
            
            // Prevent removing the owner
            if ($this->workspace->owner_id === $user->id) {
                session()->flash('error', 'Cannot remove the workspace owner.');
                return;
            }

            $invitationService->removeUser($this->workspace, $user);
            
            $this->showConfirmRemove = false;
            $this->userToRemove = null;
            
            session()->flash('success', 'User removed from workspace.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to remove user: ' . $e->getMessage());
        }
    }

    public function updateUserRole($userId, $newRole, WorkspaceInvitationService $invitationService)
    {
        try {
            $user = \App\Models\User::findOrFail($userId);
            
            // Prevent changing owner role
            if ($this->workspace->owner_id === $user->id) {
                session()->flash('error', 'Cannot change the owner\'s role.');
                return;
            }

            $invitationService->updateUserRole($this->workspace, $user, $newRole);
            
            session()->flash('success', 'User role updated successfully.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    public function resendInvitation($invitationId, WorkspaceInvitationService $invitationService)
    {
        try {
            $invitation = \App\Models\WorkspaceInvitation::findOrFail($invitationId);
            $invitationService->resendInvitation($invitation);
            
            session()->flash('success', 'Invitation resent successfully!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to resend invitation: ' . $e->getMessage());
        }
    }

    public function cancelInvitation($invitationId, WorkspaceInvitationService $invitationService)
    {
        try {
            $invitation = \App\Models\WorkspaceInvitation::findOrFail($invitationId);
            $invitationService->cancelInvitation($invitation);
            
            session()->flash('success', 'Invitation cancelled.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to cancel invitation: ' . $e->getMessage());
        }
    }

    public function confirmRemoveUser($userId)
    {
        $this->userToRemove = $userId;
        $this->showConfirmRemove = true;
    }

    public function cancelRemove()
    {
        $this->showConfirmRemove = false;
        $this->userToRemove = null;
    }

    public function getRoleOptionsProperty()
    {
        return [
            'viewer' => 'Viewer - Can view data and exports',
            'editor' => 'Editor - Can edit data and manage imports',
            'admin' => 'Admin - Can manage users and workspace settings'
        ];
    }

    public function render()
    {
        $members = $this->workspace->users()
            ->withPivot('role', 'joined_at')
            ->paginate(10);
            
        $pendingInvitations = $this->workspace->pendingInvitations()
            ->with('inviter')
            ->latest()
            ->get();

        return view('livewire.workspace-collaboration', [
            'members' => $members,
            'pendingInvitations' => $pendingInvitations,
        ]);
    }
}
