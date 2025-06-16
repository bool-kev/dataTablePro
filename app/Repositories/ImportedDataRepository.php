<?php

namespace App\Repositories;

use App\Models\ImportedData;
use App\Models\Workspace;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ImportedDataRepository
{
    public function __construct(
        private ImportedData $model
    ) {}

    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        ?string $sortBy = 'id',
        string $sortDirection = 'desc',
        array $filters = [],
        ?Workspace $workspace = null
    ): LengthAwarePaginator {
        $query = $this->model->with('importHistory');

        // Filtrer par workspace
        if ($workspace) {
            $query->whereHas('importHistory', function (Builder $q) use ($workspace) {
                $q->where('workspace_id', $workspace->id);
            });
        }

        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                // SQLite compatible search - search in the entire JSON as text
                $q->where('data', 'LIKE', "%{$search}%");
            });
        }

        // Filtres par colonnes spécifiques
        foreach ($filters as $column => $value) {
            if (!empty($value)) {
                $query->whereRaw("json_extract(data, ?) LIKE ?", ['$.' . $column, "%{$value}%"]);
            }
        }

        if ($sortBy && $sortBy !== 'id') {
            // Tri par colonne JSON (SQLite compatible)
            $query->orderByRaw("json_extract(data, ?) {$sortDirection}", ['$.' . $sortBy]);
        } else {
            $query->orderBy($sortBy ?: 'id', $sortDirection);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): ImportedData
    {
        return $this->model->create($data);
    }

    public function update(ImportedData $importedData, array $data): bool
    {
        return $importedData->update($data);
    }

    public function delete(ImportedData $importedData): bool
    {
        return $importedData->delete();
    }

    public function findById(int $id, ?Workspace $workspace = null): ?ImportedData
    {
        $query = $this->model->with('importHistory');
        
        // Si un workspace est spécifié, vérifier que la ligne appartient à ce workspace
        if ($workspace) {
            $query->whereHas('importHistory', function (Builder $q) use ($workspace) {
                $q->where('workspace_id', $workspace->id);
            });
        }
        
        return $query->find($id);
    }

    public function getUniqueColumns(?Workspace $workspace = null): array
    {
        $query = $this->model;
        
        // Filtrer par workspace
        if ($workspace) {
            $query = $query->whereHas('importHistory', function (Builder $q) use ($workspace) {
                $q->where('workspace_id', $workspace->id);
            });
        }
        
        $allData = $query->pluck('data');
        $columns = [];

        foreach ($allData as $data) {
            if (is_array($data)) {
                $columns = array_merge($columns, array_keys($data));
            }
        }

        return array_unique($columns);
    }

    public function bulkCreate(array $dataArray): bool
    {
        return $this->model->insert($dataArray);
    }

    public function getByImportHistory(int $importHistoryId): Collection
    {
        return $this->model->where('import_history_id', $importHistoryId)->get();
    }

    public function exportData(array $filters = [], ?Workspace $workspace = null): Collection
    {
        $query = $this->model->with('importHistory');

        // Filtrer par workspace
        if ($workspace) {
            $query->whereHas('importHistory', function (Builder $q) use ($workspace) {
                $q->where('workspace_id', $workspace->id);
            });
        }

        foreach ($filters as $column => $value) {
            if (!empty($value)) {
                $query->whereRaw("json_extract(data, ?) LIKE ?", ['$.' . $column, "%{$value}%"]);
            }
        }

        return $query->get();
    }

    /**
     * Count total records for a specific workspace
     */
    public function count(?Workspace $workspace = null): int
    {
        $query = $this->model;

        // Filtrer par workspace
        if ($workspace) {
            $query = $query->whereHas('importHistory', function (Builder $q) use ($workspace) {
                $q->where('workspace_id', $workspace->id);
            });
        }

        return $query->count();
    }
}
