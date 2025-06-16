<?php

namespace App\Livewire;

use App\Repositories\ImportHistoryRepository;
use App\Repositories\ImportedDataRepository;
use App\Services\WorkspaceService;
use Livewire\Component;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $currentWorkspace;

    public function mount()
    {
        $workspaceService = app(WorkspaceService::class);
        $this->currentWorkspace = $workspaceService->getCurrentWorkspace(auth()->user);
    }

    public function getStats()
    {
        if (!$this->currentWorkspace) {
            return [
                'total_rows' => 0,
                'successful_imports' => 0,
                'failed_imports' => 0,
                'unique_columns' => 0,
            ];
        }

        $importHistoryRepo = app(ImportHistoryRepository::class);
        $importedDataRepo = app(ImportedDataRepository::class);

        $totalRows = $importedDataRepo->count($this->currentWorkspace);
        $successfulImports = $importHistoryRepo->countByStatus('completed', $this->currentWorkspace);
        $failedImports = $importHistoryRepo->countByStatus('failed', $this->currentWorkspace);
        $uniqueColumns = count($importedDataRepo->getUniqueColumns($this->currentWorkspace));

        return [
            'total_rows' => $totalRows,
            'successful_imports' => $successfulImports,
            'failed_imports' => $failedImports,
            'unique_columns' => $uniqueColumns,
        ];
    }

    public function getRecentImports()
    {
        if (!$this->currentWorkspace) {
            return collect();
        }

        $importHistoryRepo = app(ImportHistoryRepository::class);
        return $importHistoryRepo->getRecentImports(5, $this->currentWorkspace);
    }

    public function getChartData()
    {
        if (!$this->currentWorkspace) {
            return [
                'imports_by_day' => [
                    'labels' => [],
                    'data' => [],
                ],
                'file_types' => [
                    'labels' => [],
                    'data' => [],
                ],
            ];
        }

        $importHistoryRepo = app(ImportHistoryRepository::class);

        // Données pour le graphique des imports par jour (7 derniers jours)
        $days = [];
        $importsCount = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('d/m');
            
            $count = $importHistoryRepo->countByDate($date->format('Y-m-d'), $this->currentWorkspace);
            $importsCount[] = $count;
        }

        // Données pour le graphique des types de fichiers
        $fileTypes = $importHistoryRepo->getFileTypeStats($this->currentWorkspace);
        
        return [
            'imports_by_day' => [
                'labels' => $days,
                'data' => $importsCount,
            ],
            'file_types' => [
                'labels' => $fileTypes->pluck('file_type')->toArray(),
                'data' => $fileTypes->pluck('count')->toArray(),
            ],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'currentWorkspace' => $this->currentWorkspace,
            'stats' => $this->getStats(),
            'recentImports' => $this->getRecentImports(),
            'chartData' => $this->getChartData(),
        ]);
    }
}
