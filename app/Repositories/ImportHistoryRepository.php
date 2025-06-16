<?php

namespace App\Repositories;

use App\Models\ImportHistory;
use App\Models\Workspace;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ImportHistoryRepository
{
    public function __construct(
        private ImportHistory $model
    ) {}

    public function create(array $data): ImportHistory
    {
        return $this->model->create($data);
    }

    public function update(ImportHistory $importHistory, array $data): bool
    {
        return $importHistory->update($data);
    }

    public function findById(int $id, ?Workspace $workspace = null): ?ImportHistory
    {
        $query = $this->model->with('importedData');
        
        if ($workspace) {
            $query->forWorkspace($workspace);
        }
        
        return $query->find($id);
    }

    public function getAll(?Workspace $workspace = null): Collection
    {
        $query = $this->model->with('importedData')
            ->orderBy('created_at', 'desc');
        
        if ($workspace) {
            $query->forWorkspace($workspace);
        }
        
        return $query->get();
    }

    public function paginate(int $perPage = 15, ?Workspace $workspace = null): LengthAwarePaginator
    {
        $query = $this->model->with('importedData')
            ->orderBy('created_at', 'desc');
        
        if ($workspace) {
            $query->forWorkspace($workspace);
        }
        
        return $query->paginate($perPage);
    }

    public function getStatistics(?Workspace $workspace = null): array
    {
        $query = $this->model;
        
        if ($workspace) {
            $query = $query->forWorkspace($workspace);
        }

        $total = $query->count();
        $successful = $query->where('status', 'completed')->count();
        $failed = $query->where('status', 'failed')->count();
        $pending = $query->where('status', 'pending')->count();
        $processing = $query->where('status', 'processing')->count();

        $totalRows = $query->sum('total_rows');
        $successfulRows = $query->sum('successful_rows');
        $failedRows = $query->sum('failed_rows');

        return [
            'total_imports' => $total,
            'successful_imports' => $successful,
            'failed_imports' => $failed,
            'pending_imports' => $pending,
            'processing_imports' => $processing,
            'total_rows' => $totalRows,
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
            'success_rate' => $totalRows > 0 ? ($successfulRows / $totalRows) * 100 : 0,
        ];
    }

    public function getRecentImports(int $limit = 10, ?Workspace $workspace = null): Collection
    {
        $query = $this->model->with('importedData')
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($workspace) {
            $query->forWorkspace($workspace);
        }

        return $query->get();
    }

    /**
     * Count imports by status for a specific workspace
     */
    public function countByStatus(string $status, ?Workspace $workspace = null): int
    {
        $query = $this->model->where('status', $status);

        if ($workspace) {
            $query->forWorkspace($workspace);
        }

        return $query->count();
    }

    /**
     * Count imports by date for a specific workspace
     */
    public function countByDate(string $date, ?Workspace $workspace = null): int
    {
        $query = $this->model->whereDate('created_at', $date);

        if ($workspace) {
            $query->forWorkspace($workspace);
        }

        return $query->count();
    }

    /**
     * Get file type statistics for a specific workspace
     */
    public function getFileTypeStats(?Workspace $workspace = null): \Illuminate\Support\Collection
    {
        $query = $this->model->selectRaw('file_type, COUNT(*) as count')
            ->groupBy('file_type');

        if ($workspace) {
            $query->forWorkspace($workspace);
        }

        $results = $query->get();
        
        // Normaliser les types de fichiers pour l'affichage
        return $results->map(function ($item) {
            $normalizedType = match(strtolower($item->file_type)) {
                'csv' => 'CSV',
                'xlsx', 'xls' => 'Excel',
                default => ucfirst($item->file_type)
            };
            
            return (object) [
                'file_type' => $normalizedType,
                'count' => $item->count
            ];
        })->groupBy('file_type')->map(function ($items) {
            return (object) [
                'file_type' => $items->first()->file_type,
                'count' => $items->sum('count')
            ];
        })->values();
    }
}
