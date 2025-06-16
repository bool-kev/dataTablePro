<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Services\WorkspaceInvitationService;
use Illuminate\Database\Seeder;

class TestCollaborationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des utilisateurs de test
        $owner = User::firstOrCreate(
            ['email' => 'owner@datatable.com'],
            [
                'name' => 'John Owner',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $collaborator = User::firstOrCreate(
            ['email' => 'collaborator@datatable.com'],
            [
                'name' => 'Jane Collaborator',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Créer un workspace de test
        $workspace = Workspace::firstOrCreate(
            ['name' => 'Test Collaboration Workspace'],
            [
                'slug' => 'test-collaboration-workspace-abc123',
                'description' => 'Workspace for testing collaboration features',
                'owner_id' => $owner->id,
                'is_active' => true,
                'last_accessed_at' => now(),
            ]
        );

        // Ajouter le collaborateur au workspace avec le rôle editor
        $workspace->users()->syncWithoutDetaching([
            $collaborator->id => [
                'role' => 'editor',
                'joined_at' => now()->subDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Créer quelques invitations de test
        $pendingInvitations = [
            'viewer@example.com' => 'viewer',
            'editor@example.com' => 'editor',
            'admin@example.com' => 'admin',
        ];

        foreach ($pendingInvitations as $email => $role) {
            WorkspaceInvitation::firstOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'email' => $email,
                ],
                [
                    'inviter_id' => $owner->id,
                    'role' => $role,
                    'token' => \Illuminate\Support\Str::random(64),
                    'status' => 'pending',
                    'expires_at' => now()->addDays(7),
                ]
            );
        }

        // Créer une invitation expirée
        WorkspaceInvitation::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'email' => 'expired@example.com',
            ],
            [
                'inviter_id' => $owner->id,
                'role' => 'viewer',
                'token' => \Illuminate\Support\Str::random(64),
                'status' => 'pending',
                'expires_at' => now()->subDays(1),
            ]
        );

        $this->command->info('Test collaboration data created:');
        $this->command->info("- Owner: {$owner->email} (password: password)");
        $this->command->info("- Collaborator: {$collaborator->email} (password: password)");
        $this->command->info("- Workspace: {$workspace->name}");
        $this->command->info("- Pending invitations: " . count($pendingInvitations));
    }
}
