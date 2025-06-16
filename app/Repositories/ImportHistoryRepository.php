<?php

namespace App\Repositories;

use App\Models\ImportHistory;
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

    public function findById(int $id): ?ImportHistory
    {
        return $this->model->with('importedData')->find($id);
    }

    public function getAll(): Collection
    {
        return $this->model->with('importedData')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('importedData')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getStatistics(): array
    {
        $total = $this->model->count();
        $successful = $this->model->where('status', 'completed')->count();
        $failed = $this->model->where('status', 'failed')->count();
        $pending = $this->model->where('status', 'pending')->count();
        $processing = $this->model->where('status', 'processing')->count();

        $totalRows = $this->model->sum('total_rows');
        $successfulRows = $this->model->sum('successful_rows');
        $failedRows = $this->model->sum('failed_rows');

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

    public function getRecentImports(int $limit = 10, ?\App\Models\Workspace $workspace = null): Collection
    {
        $query = $this->model->with('importedData')
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($workspace) {
            $query->where('workspace_id', $workspace->id);
        }

        return $query->get();
    }

    /**
     * Count imports by status for a specific workspace
     */
    public function countByStatus(string $status, ?\App\Models\Workspace $workspace = null): int
    {
        $query = $this->model->where('status', $status);

        if ($workspace) {
            $query->where('workspace_id', $workspace->id);
        }

        return $query->count();
    }

    /**
     * Count imports by date for a specific workspace
     */
    public function countByDate(string $date, ?\App\Models\Workspace $workspace = null): int
    {
        $query = $this->model->whereDate('created_at', $date);

        if ($workspace) {
            $query->where('workspace_id', $workspace->id);
        }

        return $query->count();
    }

    /**
     * Get file type statistics for a specific workspace
     */
    public function getFileTypeStats(?\App\Models\Workspace $workspace = null): \Illuminate\Support\Collection
    {
        $query = $this->model->selectRaw('file_type, COUNT(*) as count')
            ->groupBy('file_type');

        if ($workspace) {
            $query->where('workspace_id', $workspace->id);
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
