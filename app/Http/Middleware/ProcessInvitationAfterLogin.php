<?php

namespace App\Http\Middleware;

use App\Services\WorkspaceInvitationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProcessInvitationAfterLogin
{
    public function __construct(
        private WorkspaceInvitationService $invitationService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && session()->has('invitation_token')) {
            $token = session('invitation_token');
            session()->forget('invitation_token');
            
            try {
                $success = $this->invitationService->acceptInvitation($token, Auth::user());
                
                if ($success) {
                    $invitation = \App\Models\WorkspaceInvitation::where('token', $token)->first();
                    if ($invitation) {
                        session()->flash('success', "You've successfully joined {$invitation->workspace->name}!");
                    }
                } else {
                    session()->flash('error', 'Failed to accept invitation.');
                }
            } catch (\Exception $e) {
                session()->flash('error', $e->getMessage());
            }
        }

        return $next($request);
    }
}
