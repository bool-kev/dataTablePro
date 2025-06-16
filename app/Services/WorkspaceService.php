<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use App\Repositories\WorkspaceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkspaceService
{
    public function __construct(
        private WorkspaceRepository $workspaceRepository
    ) {}

    public function createWorkspace(User $user, array $data): Workspace
    {
        return DB::transaction(function () use ($user, $data) {
            $workspace = $this->workspaceRepository->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'slug' => $this->generateUniqueSlug($data['name']),
                'owner_id' => $user->id,
            ]);

            $this->workspaceRepository->addUserToWorkspace($workspace, $user, 'owner');
            return $workspace;
        });
    }

    public function switchWorkspace(User $user, Workspace $workspace): bool
    {
        if (!$workspace->canUserAccess($user)) {
            return false;
        }

        session(['current_workspace_id' => $workspace->id]);
        $this->workspaceRepository->updateLastAccessed($workspace);
        return true;
    }

    public function deleteWorkspace(Workspace $workspace): bool
    {
        return DB::transaction(function () use ($workspace) {
            return $workspace->delete();
        });
    }

    public function setCurrentWorkspace(User $user, Workspace $workspace): bool
    {
        return $this->switchWorkspace($user, $workspace);
    }

    public function inviteUserToWorkspace(Workspace $workspace, string $email, string $role = 'viewer'): bool
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false;
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

    public function getCurrentWorkspace(User $user): ?Workspace
    {
        return $user->getCurrentWorkspace();
    }

    public function findById(int $workspaceId): ?Workspace
    {
        return $this->workspaceRepository->findById($workspaceId);
    }

    public function getUserWorkspaces(User $user)
    {
        return $this->workspaceRepository->getUserWorkspaces($user);
    }

    public function getWorkspaceStatistics(Workspace $workspace): array
    {
        return $this->workspaceRepository->getWorkspaceStatistics($workspace);
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
}