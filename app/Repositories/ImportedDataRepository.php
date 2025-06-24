<?php

namespace App\Repositories;

use App\Models\ImportedData;
use App\Models\ImportHistory;
use App\Models\Workspace;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;


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

        // Filtrer par workspace (requis)
        if ($workspace) {
            $query->forWorkspace($workspace);
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
            $query->forWorkspace($workspace);
        }
        
        return $query->find($id);
    }

    public function getUniqueColumns(?Workspace $workspace = null): array
    {
        $query = $this->model;
        
        // Filtrer par workspace (requis)
        if ($workspace) {
            $query = $query->forWorkspace($workspace);
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

    public function exportData(array $filters = [], ?Workspace $workspace = null, ?string $search = null): Collection
    {
        $query = $this->model->with('importHistory');

        // Filtrer par workspace (requis)
        if ($workspace) {
            $query->forWorkspace($workspace);
        }

        // Recherche globale dans toutes les colonnes
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

        return $query->get();
    }

    /**
     * Count total records for a specific workspace
     */
    public function count(?Workspace $workspace = null): int
    {
        $query = $this->model;

        // Filtrer par workspace (requis)
        if ($workspace) {
            $query = $query->forWorkspace($workspace);
        }

        return $query->count();
    }

    /**
     * Get column statistics for a specific column
     */
    public function getColumnStats(?Workspace $workspace = null, string $column = 'name'): array
    {
        $query = $this->model;
        
        if ($workspace) {
            $query = $query->forWorkspace($workspace);
        }
        
        $data = $query->pluck('data');
        $totalCount = $data->count();
        $nonNullCount = $data->filter(function ($item) use ($column) {
            return isset($item[$column]) && $item[$column] !== null;
        })->count();
        
        return [
            'total_count' => $totalCount,
            'non_null_count' => $nonNullCount,
            'null_count' => $totalCount - $nonNullCount,
            'completion_rate' => $totalCount > 0 ? ($nonNullCount / $totalCount) * 100 : 0
        ];
    }

    /**
     * Detect column data types
     */
    public function detectColumnTypes(?Workspace $workspace = null): array
    {
        $query = $this->model;
        
        if ($workspace) {
            $query = $query->forWorkspace($workspace);
        }
        
        $allData = $query->pluck('data');
        $columns = $this->getUniqueColumns($workspace);
        $types = [];
        
        foreach ($columns as $column) {
            $values = $allData->map(function ($data) use ($column) {
                return $data[$column] ?? null;
            })->filter();
            
            if ($values->isEmpty()) {
                $types[$column] = 'unknown';
                continue;
            }
            
            // Check if numeric
            $numericCount = $values->filter(function ($value) {
                return is_numeric($value);
            })->count();
            
            if ($numericCount / $values->count() > 0.8) {
                $types[$column] = 'numeric';
            } elseif ($values->filter(function ($value) {
                return filter_var($value, FILTER_VALIDATE_EMAIL);
            })->count() > 0) {
                $types[$column] = 'email';
            } else {
                $types[$column] = 'text';
            }
        }
        
        return $types;
    }

    /**
     * Bulk insert data efficiently
     */
    public function bulkInsert(array $data): bool
    {
        try {
            // Préparer les données pour l'insertion
            $processedData = [];
            
            foreach ($data as $item) {
                // Convertir les données en JSON si nécessaire
                $processedItem = $item;
                if (isset($processedItem['data']) && is_array($processedItem['data'])) {
                    $processedItem['data'] = json_encode($processedItem['data']);
                }
                $processedData[] = $processedItem;
            }
            
            $this->model->insert($processedData);
            return true;
        } catch (\Exception $e) {
            // Pour debug - à retirer en production
            Log::error('Bulk insert failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk delete by IDs
     */
    public function bulkDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    /**
     * Bulk update data
     */
    public function bulkUpdate(array $ids, array $updates): int
    {
        // Pour les updates dans les données JSON, nous devons faire cela individuellement
        // car SQLite ne supporte pas les JSON updates complexes en bulk
        $updated = 0;
        
        foreach ($ids as $id) {
            $record = $this->model->find($id);
            if ($record) {
                $currentData = $record->data;
                $newData = array_merge($currentData, $updates);
                
                $record->update([
                    'data' => $newData,
                    'row_hash' => md5(json_encode($newData))
                ]);
                $updated++;
            }
        }
        
        return $updated;
    }

    /**
     * Check if data with given hash already exists
     */
    public function isDuplicate(string $hash, ?Workspace $workspace = null): bool
    {
        $query = $this->model->where('row_hash', $hash);
        
        if ($workspace) {
            $query = $query->forWorkspace($workspace);
        }
        
        return $query->exists();
    }

    /**
     * Find duplicate records within a workspace
     */
    public function findDuplicates(?Workspace $workspace = null): Collection
    {
        $duplicateHashes = $this->model;
        
        if ($workspace) {
            $duplicateHashes = $duplicateHashes->forWorkspace($workspace);
        }
        
        $duplicateHashes = $duplicateHashes->select('row_hash')
            ->groupBy('row_hash')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('row_hash');
        
        $duplicateQuery = $this->model->whereIn('row_hash', $duplicateHashes);
        
        if ($workspace) {
            $duplicateQuery = $duplicateQuery->forWorkspace($workspace);
        }
        
        return $duplicateQuery->get();
    }

    /**
     * Get data for export with optional column selection
     */
    public function getForExport(?Workspace $workspace = null, array $columns = []): array
    {
        $query = $this->model;
        
        if ($workspace) {
            $query = $query->forWorkspace($workspace);
        }
        
        $data = $query->get();
        
        return $data->map(function ($item) use ($columns) {
            $rowData = $item->data;
            
            if (empty($columns)) {
                return $rowData;
            }
            
            // Filtrer seulement les colonnes demandées
            return array_intersect_key($rowData, array_flip($columns));
        })->toArray();
    }

    /**
     * Get data for a specific workspace with pagination
     */
    public function getForWorkspace(
        int $workspaceId,
        string $search = '',
        array $filters = [],
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        int $perPage = 15
    ): LengthAwarePaginator {
        $workspace = Workspace::find($workspaceId);
        
        return $this->paginate(
            $perPage,
            $search,
            $sortBy,
            $sortDirection,
            $filters,
            $workspace
        );
    }

    /**
     * Find a specific record by ID
     */
    public function find(int $id): ?ImportedData
    {
        return $this->model->find($id);
    }

    /**
     * Update a record by ID
     */
    public function updateById(int $id, array $data): bool
    {
        $record = $this->model->find($id);
        if (!$record) {
            return false;
        }
        
        return $this->update($record, $data);
    }

    /**
     * Delete a record by ID
     */
    public function deleteById(int $id): bool
    {
        $record = $this->model->find($id);
        if (!$record) {
            return false;
        }
        
        return $record->delete();
    }

    /**
     * Get total count of data for a workspace
     */
    public function getWorkspaceDataCount(int $workspaceId): int
    {
        $workspace = Workspace::find($workspaceId);
        if (!$workspace) {
            return 0;
        }
        
        return $this->model->forWorkspace($workspace)->count();
    }

    /**
     * Delete all imported data for a specific import history
     */
    public function deleteByImportHistory(ImportHistory $importHistory): int
    {
        return $this->model->where('import_history_id', $importHistory->id)->delete();
    }
}
