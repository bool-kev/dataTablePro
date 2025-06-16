<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WorkspaceRepository
{
    public function __construct(
        private Workspace $model
    ) {}

    public function create(array $data): Workspace
    {
        return $this->model->create($data);
    }

    public function update(Workspace $workspace, array $data): bool
    {
        return $workspace->update($data);
    }

    public function delete(Workspace $workspace): bool
    {
        return $workspace->delete();
    }

    public function findById(int $id): ?Workspace
    {
        return $this->model->with(['owner', 'users'])->find($id);
    }

    public function findBySlug(string $slug): ?Workspace
    {
        return $this->model->with(['owner', 'users'])->where('slug', $slug)->first();
    }

    public function getUserWorkspaces(User $user): Collection
    {
        return $user->workspaces()
            ->with(['owner'])
            ->where('is_active', true)
            ->orderBy('last_accessed_at', 'desc')
            ->get();
    }

    public function getOwnedWorkspaces(User $user): Collection
    {
        return $user->ownedWorkspaces()
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function paginate(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->workspaces()
            ->with(['owner'])
            ->where('is_active', true)
            ->orderBy('last_accessed_at', 'desc')
            ->paginate($perPage);
    }

    public function addUserToWorkspace(Workspace $workspace, User $user, string $role = 'viewer'): bool
    {
        if ($workspace->users()->where('user_id', $user->id)->exists()) {
            return false; // User already has access
        }

        $workspace->users()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
        ]);

        return true;
    }

    public function updateUserRole(Workspace $workspace, User $user, string $role): bool
    {
        return $workspace->users()->updateExistingPivot($user->id, ['role' => $role]);
    }

    public function removeUserFromWorkspace(Workspace $workspace, User $user): bool
    {
        return $workspace->users()->detach($user->id);
    }

    public function updateLastAccessed(Workspace $workspace): void
    {
        $workspace->update(['last_accessed_at' => now()]);
    }

    public function getWorkspaceUsers(Workspace $workspace): Collection
    {
        return $workspace->users()
            ->withPivot('role', 'joined_at')
            ->orderBy('pivot_joined_at', 'desc')
            ->get();
    }

    public function searchWorkspaces(User $user, string $search): Collection
    {
        return $user->workspaces()
            ->with(['owner'])
            ->where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('last_accessed_at', 'desc')
            ->get();
    }

    public function getWorkspaceStatistics(Workspace $workspace): array
    {
        $totalImports = $workspace->importHistories()->count();
        $successfulImports = $workspace->importHistories()->where('status', 'completed')->count();
        $totalRows = $workspace->importHistories()->sum('total_rows');
        $successfulRows = $workspace->importHistories()->sum('successful_rows');

        return [
            'total_imports' => $totalImports,
            'successful_imports' => $successfulImports,
            'total_rows' => $totalRows,
            'successful_rows' => $successfulRows,
            'success_rate' => $totalRows > 0 ? ($successfulRows / $totalRows) * 100 : 0,
            'total_users' => $workspace->users()->count() + 1, // +1 for owner
        ];
    }
}