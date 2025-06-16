<?php

namespace App\Mail;

use App\Models\WorkspaceInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkspaceInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public WorkspaceInvitation $invitation;

    /**
     * Create a new message instance.
     */
    public function __construct(WorkspaceInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited to join {$this->invitation->workspace->name} on DataTable Pro",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.workspace-invitation',
            with: [
                'invitation' => $this->invitation,
                'workspace' => $this->invitation->workspace,
                'inviter' => $this->invitation->inviter,
                'acceptUrl' => route('workspace.invitation.accept', $this->invitation->token),
                'declineUrl' => route('workspace.invitation.decline', $this->invitation->token),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
}
