<?php

namespace App\Console\Commands;

use App\Models\WorkspaceInvitation;
use Illuminate\Console\Command;

class GenerateInvitationLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invitation:link {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a test invitation link for collaboration testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'viewer@example.com';
        
        $invitation = WorkspaceInvitation::where('email', $email)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            $this->error("No pending invitation found for {$email}");
            $this->info("Available pending invitations:");
            
            $pendingInvitations = WorkspaceInvitation::where('status', 'pending')
                ->with(['workspace', 'inviter'])
                ->get();
                
            foreach ($pendingInvitations as $inv) {
                $this->info("- {$inv->email} ({$inv->role}) for workspace '{$inv->workspace->name}'");
            }
            
            return 1;
        }

        $url = route('workspace.invitation.show', $invitation->token);
        
        $this->info("Invitation link for {$email}:");
        $this->line($url);
        $this->info("");
        $this->info("Invitation details:");
        $this->info("- Workspace: {$invitation->workspace->name}");
        $this->info("- Role: {$invitation->role}");
        $this->info("- Invited by: {$invitation->inviter->name}");
        $this->info("- Expires: {$invitation->expires_at->format('Y-m-d H:i:s')}");
        
        return 0;
    }
}
