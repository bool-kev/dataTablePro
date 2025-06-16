<?php

namespace App\Http\Controllers;

use App\Models\WorkspaceInvitation;
use App\Services\WorkspaceInvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class WorkspaceInvitationController extends Controller
{
    public function __construct(
        private WorkspaceInvitationService $invitationService
    ) {}

    /**
     * Show invitation acceptance page
     */
    public function show(string $token)
    {
        $invitation = WorkspaceInvitation::where('token', $token)
            ->with(['workspace', 'inviter'])
            ->first();

        if (!$invitation) {
            return view('workspace.invitation.invalid')->with('error', 'Invalid invitation token.');
        }

        if (!$invitation->isPending()) {
            $status = $invitation->isExpired() ? 'expired' : $invitation->status;
            return view('workspace.invitation.invalid')->with([
                'error' => 'This invitation is no longer valid.',
                'status' => $status
            ]);
        }

        return view('workspace.invitation.show', compact('invitation'));
    }

    /**
     * Accept invitation
     */
    public function accept(string $token)
    {
        try {
            if (!Auth::check()) {
                // Store the token in session and redirect to login/register
                session(['invitation_token' => $token]);
                return redirect()->route('login')
                    ->with('message', 'Please login or create an account to accept the invitation.');
            }

            $success = $this->invitationService->acceptInvitation($token, Auth::user());
            
            if ($success) {
                $invitation = WorkspaceInvitation::where('token', $token)->first();
                return redirect()->route('dashboard')
                    ->with('success', "You've successfully joined {$invitation->workspace->name}!");
            }

            return redirect()->back()->with('error', 'Failed to accept invitation.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Decline invitation
     */
    public function decline(string $token)
    {
        try {
            $invitation = WorkspaceInvitation::where('token', $token)->first();
            
            if (!$invitation) {
                return view('workspace.invitation.invalid')->with('error', 'Invalid invitation token.');
            }

            $workspaceName = $invitation->workspace->name;
            $this->invitationService->declineInvitation($token);
            
            return view('workspace.invitation.declined', compact('workspaceName'));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Process invitation after login (called from auth middleware)
     */
    public function processAfterLogin()
    {
        $token = session('invitation_token');
        
        if (!$token) {
            return redirect()->route('dashboard');
        }

        session()->forget('invitation_token');
        
        try {
            $success = $this->invitationService->acceptInvitation($token, Auth::user());
            
            if ($success) {
                $invitation = WorkspaceInvitation::where('token', $token)->first();
                return redirect()->route('dashboard')
                    ->with('success', "You've successfully joined {$invitation->workspace->name}!");
            }

            return redirect()->route('dashboard')->with('error', 'Failed to accept invitation.');
            
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        }
    }
}
