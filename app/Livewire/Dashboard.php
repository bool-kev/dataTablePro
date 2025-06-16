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

    protected ImportHistoryRepository $importHistoryRepository;
    protected ImportedDataRepository $importedDataRepository;
    protected WorkspaceService $workspaceService;

    public function boot(
        ImportHistoryRepository $importHistoryRepository,
        ImportedDataRepository $importedDataRepository,
        WorkspaceService $workspaceService
    ) {
        $this->importHistoryRepository = $importHistoryRepository;
        $this->importedDataRepository = $importedDataRepository;
        $this->workspaceService = $workspaceService;
    }

    public function mount()
    {
        $this->currentWorkspace = $this->workspaceService->getCurrentWorkspace(auth()->user());
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

        $totalRows = $this->importedDataRepository->count($this->currentWorkspace);
        $successfulImports = $this->importHistoryRepository->countByStatus('completed', $this->currentWorkspace);
        $failedImports = $this->importHistoryRepository->countByStatus('failed', $this->currentWorkspace);
        $uniqueColumns = count($this->importedDataRepository->getUniqueColumns($this->currentWorkspace));

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

        return $this->importHistoryRepository->getRecentImports(5, $this->currentWorkspace);
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

        // Données pour le graphique des imports par jour (7 derniers jours)
        $days = [];
        $importsCount = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('d/m');
            
            $count = $this->importHistoryRepository->countByDate($date->format('Y-m-d'), $this->currentWorkspace);
            $importsCount[] = $count;
        }

        // Données pour le graphique des types de fichiers
        $fileTypes = $this->importHistoryRepository->getFileTypeStats($this->currentWorkspace);
        
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
