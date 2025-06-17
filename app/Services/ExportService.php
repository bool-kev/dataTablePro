<?php

namespace App\Services;

use App\Models\Workspace;
use App\Repositories\ImportedDataRepository;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportService
{
    public function __construct(
        private ImportedDataRepository $importedDataRepository
    ) {}

    public function exportToCsv(array $filters = [], ?Workspace $workspace = null, ?string $search = null): string
    {
        $data = $this->importedDataRepository->exportData($filters, $workspace, $search);
        
        if ($data->isEmpty()) {
            throw new \Exception('Aucune donnée à exporter');
        }

        $exportData = $this->prepareExportData($data);
        
        $filename = 'export_' . date('Y-m-d_H-i-s') . '.csv';
        $filePath = storage_path('app/public/exports/' . $filename);
        
        // Créer le dossier s'il n'existe pas
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $export = new class($exportData) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private array $data) {}

            public function collection(): Collection
            {
                return collect($this->data['rows']);
            }

            public function headings(): array
            {
                return $this->data['headings'];
            }
        };

        Excel::store($export, 'exports/' . $filename, 'public');

        return $filename;
    }

    public function exportToExcel(array $filters = [], ?Workspace $workspace = null, ?string $search = null): string
    {
        $data = $this->importedDataRepository->exportData($filters, $workspace, $search);
        
        if ($data->isEmpty()) {
            throw new \Exception('Aucune donnée à exporter');
        }

        $exportData = $this->prepareExportData($data);
        
        $filename = 'export_' . date('Y-m-d_H-i-s') . '.xlsx';

        $export = new class($exportData) implements 
            \Maatwebsite\Excel\Concerns\FromCollection, 
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithStyles,
            \Maatwebsite\Excel\Concerns\ShouldAutoSize {
            
            public function __construct(private array $data) {}

            public function collection(): Collection
            {
                return collect($this->data['rows']);
            }

            public function headings(): array
            {
                return $this->data['headings'];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        };

        Excel::store($export, 'exports/' . $filename, 'public');

        return $filename;
    }

    private function prepareExportData(Collection $data): array
    {
        if ($data->isEmpty()) {
            return ['headings' => [], 'rows' => []];
        }

        // Obtenir toutes les colonnes uniques
        $allColumns = [];
        foreach ($data as $item) {
            $allColumns = array_merge($allColumns, array_keys($item->data));
        }
        $allColumns = array_unique($allColumns);

        // Préparer les données
        $rows = [];
        foreach ($data as $item) {
            $row = [];
            foreach ($allColumns as $column) {
                $row[] = $item->data[$column] ?? '';
            }
            $rows[] = $row;
        }

        return [
            'headings' => $allColumns,
            'rows' => $rows,
        ];
    }

    public function exportToJson(array $filters = [], ?Workspace $workspace = null, ?string $search = null): string
    {
        $data = $this->importedDataRepository->exportData($filters, $workspace, $search);
        
        if ($data->isEmpty()) {
            throw new \Exception('Aucune donnée à exporter');
        }

        $exportData = [];
        foreach ($data as $item) {
            $exportData[] = $item->data;
        }

        $filename = 'export_' . date('Y-m-d_H-i-s') . '.json';
        $filePath = 'exports/' . $filename;
        
        // Créer le dossier s'il n'existe pas
        $fullPath = storage_path('app/public/exports/');
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        Storage::disk('public')->put($filePath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $filename;
    }

    /**
     * Export and download CSV directly
     */
    public function downloadCsv(array $filters = [], ?Workspace $workspace = null): BinaryFileResponse
    {
        $filename = $this->exportToCsv($filters, $workspace);
        $filePath = 'exports/' . $filename;
        $fullPath = Storage::disk('public')->path($filePath);
        
        return response()->download($fullPath, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Export and download Excel directly
     */
    public function downloadExcel(array $filters = [], ?Workspace $workspace = null): BinaryFileResponse
    {
        $filename = $this->exportToExcel($filters, $workspace);
        $filePath = 'exports/' . $filename;
        $fullPath = Storage::disk('public')->path($filePath);
        
        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export and download JSON directly
     */
    public function downloadJson(array $filters = [], ?Workspace $workspace = null): BinaryFileResponse
    {
        $filename = $this->exportToJson($filters, $workspace);
        $filePath = 'exports/' . $filename;
        $fullPath = Storage::disk('public')->path($filePath);
        
        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
}
