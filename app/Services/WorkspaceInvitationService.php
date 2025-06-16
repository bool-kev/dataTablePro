<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\WorkspaceInvitationMail;

class WorkspaceInvitationService
{
    /**
     * Send an invitation to join a workspace
     */
    public function inviteUser(Workspace $workspace, string $email, string $role, User $inviter): WorkspaceInvitation
    {
        // Check if user is already a member
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $workspace->users()->where('user_id', $existingUser->id)->exists()) {
            throw new \Exception('User is already a member of this workspace.');
        }

        // Check if there's already a pending invitation
        $existingInvitation = WorkspaceInvitation::where([
            'workspace_id' => $workspace->id,
            'email' => $email,
            'status' => 'pending'
        ])->first();

        if ($existingInvitation && !$existingInvitation->isExpired()) {
            throw new \Exception('A pending invitation already exists for this email.');
        }

        // Create new invitation
        $invitation = WorkspaceInvitation::create([
            'workspace_id' => $workspace->id,
            'inviter_id' => $inviter->id,
            'email' => $email,
            'role' => $role,
        ]);

        // Send invitation email
        try {
            Mail::to($email)->send(new WorkspaceInvitationMail($invitation));
        } catch (\Exception $e) {
            // Log the error but don't fail the invitation creation
            Log::error('Failed to send invitation email: ' . $e->getMessage());
        }

        return $invitation;
    }

    /**
     * Accept an invitation
     */
    public function acceptInvitation(string $token, User $user): bool
    {
        $invitation = WorkspaceInvitation::where('token', $token)->first();

        if (!$invitation) {
            throw new \Exception('Invalid invitation token.');
        }

        if (!$invitation->isPending()) {
            throw new \Exception('This invitation is no longer valid.');
        }

        if ($invitation->email !== $user->email) {
            throw new \Exception('This invitation is not for your email address.');
        }

        return DB::transaction(function () use ($invitation, $user) {
            return $invitation->accept($user);
        });
    }

    /**
     * Decline an invitation
     */
    public function declineInvitation(string $token): bool
    {
        $invitation = WorkspaceInvitation::where('token', $token)->first();

        if (!$invitation) {
            throw new \Exception('Invalid invitation token.');
        }

        if (!$invitation->isPending()) {
            throw new \Exception('This invitation is no longer valid.');
        }

        return $invitation->decline();
    }

    /**
     * Resend an invitation
     */
    public function resendInvitation(WorkspaceInvitation $invitation): void
    {
        if (!$invitation->isPending()) {
            throw new \Exception('Cannot resend a non-pending invitation.');
        }

        // Extend expiration by 7 days
        $invitation->update([
            'expires_at' => now()->addDays(7)
        ]);

        // Resend email
        try {
            Mail::to($invitation->email)->send(new WorkspaceInvitationMail($invitation));
        } catch (\Exception $e) {
            Log::error('Failed to resend invitation email: ' . $e->getMessage());
            throw new \Exception('Failed to send invitation email.');
        }
    }

    /**
     * Cancel an invitation
     */
    public function cancelInvitation(WorkspaceInvitation $invitation): bool
    {
        if (!$invitation->isPending()) {
            throw new \Exception('Cannot cancel a non-pending invitation.');
        }

        return $invitation->update(['status' => 'expired']);
    }

    /**
     * Remove a user from workspace
     */
    public function removeUser(Workspace $workspace, User $user): bool
    {
        return $workspace->users()->detach($user->id) > 0;
    }

    /**
     * Update user role in workspace
     */
    public function updateUserRole(Workspace $workspace, User $user, string $role): bool
    {
        return $workspace->users()->updateExistingPivot($user->id, ['role' => $role]) > 0;
    }

    /**
     * Get available roles
     */
    public function getAvailableRoles(): array
    {
        return [
            'viewer' => 'Viewer - Can view data and exports',
            'editor' => 'Editor - Can edit data and manage imports',
            'admin' => 'Admin - Can manage users and workspace settings'
        ];
    }
}
