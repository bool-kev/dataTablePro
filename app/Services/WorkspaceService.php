<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use App\Repositories\WorkspaceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WorkspaceService
{
    public function __construct(
        private WorkspaceRepository $workspaceRepository
    ) {}

    public function createWorkspace(User $user, array $data): Workspace
    {
        return DB::transaction(function () use ($user, $data) {
            // Créer le workspace
            $workspace = $this->workspaceRepository->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'slug' => $this->generateUniqueSlug($data['name']),
                'database_type' => $data['database_type'] ?? 'sqlite',
                'owner_id' => $user->id,
            ]);

            // Créer la base de données
            $this->createWorkspaceDatabase($workspace);

            // Ajouter l'utilisateur propriétaire au workspace
            $this->workspaceRepository->addUserToWorkspace($workspace, $user, 'owner');

            // Créer les tables dans la nouvelle base de données
            $this->createWorkspaceTables($workspace);

            return $workspace;
        });
    }

    public function switchWorkspace(User $user, Workspace $workspace): bool
    {
        if (!$workspace->canUserAccess($user)) {
            return false;
        }

        // Mettre à jour la session
        session(['current_workspace_id' => $workspace->id]);

        // Mettre à jour la date de dernier accès
        $this->workspaceRepository->updateLastAccessed($workspace);

        // Configurer la connexion à la base de données du workspace
        $workspace->setupDatabaseConnection();

        return true;
    }

    public function deleteWorkspace(Workspace $workspace): bool
    {
        return DB::transaction(function () use ($workspace) {
            // Supprimer le fichier de base de données si c'est SQLite
            if ($workspace->database_type === 'sqlite') {
                $dbPath = $workspace->getDatabasePath();
                if (file_exists($dbPath)) {
                    unlink($dbPath);
                }
            }

            // Supprimer le workspace
            return $this->workspaceRepository->delete($workspace);
        });
    }

    public function inviteUserToWorkspace(Workspace $workspace, string $email, string $role = 'viewer'): bool
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false; // L'utilisateur n'existe pas
        }

        return $this->workspaceRepository->addUserToWorkspace($workspace, $user, $role);
    }

    public function updateUserRole(Workspace $workspace, User $user, string $role): bool
    {
        return $this->workspaceRepository->updateUserRole($workspace, $user, $role);
    }

    public function removeUserFromWorkspace(Workspace $workspace, User $user): bool
    {
        return $this->workspaceRepository->removeUserFromWorkspace($workspace, $user);
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Workspace::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function createWorkspaceDatabase(Workspace $workspace): void
    {
        if ($workspace->database_type === 'sqlite') {
            $workspace->createDatabase();
        }

        // Pour d'autres types de bases de données, implémenter la logique appropriée
    }

    private function createWorkspaceTables(Workspace $workspace): void
    {
        $connectionName = $workspace->getDatabaseConnectionName();
        
        // Configurer la connexion
        $workspace->setupDatabaseConnection();

        // Créer les tables nécessaires dans la base de données du workspace
        Schema::connection($connectionName)->create('import_histories', function ($table) {
            $table->id();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('file_type');
            $table->integer('total_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->json('errors')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::connection($connectionName)->create('imported_data', function ($table) {
            $table->id();
            $table->foreignId('import_history_id')->constrained()->onDelete('cascade');
            $table->json('data');
            $table->string('row_hash')->unique();
            $table->timestamps();
            
            $table->index(['import_history_id']);
        });
    }

    public function getCurrentWorkspace(User $user): ?Workspace
    {
        $currentWorkspace = $user->getCurrentWorkspace();
        
        if ($currentWorkspace) {
            // Configurer la connexion à la base de données
            $currentWorkspace->setupDatabaseConnection();
        }
        
        return $currentWorkspace;
    }

    public function getWorkspaceStatistics(Workspace $workspace): array
    {
        return $this->workspaceRepository->getWorkspaceStatistics($workspace);
    }
}