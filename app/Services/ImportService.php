<?php

namespace App\Services;

use App\Models\ImportHistory;
use App\Models\Workspace;
use App\Repositories\ImportedDataRepository;
use App\Repositories\ImportHistoryRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class ImportService
{
    public function __construct(
        private ImportedDataRepository $importedDataRepository,
        private ImportHistoryRepository $importHistoryRepository
    ) {}

    public function processFile(UploadedFile $file, ?Workspace $workspace = null): ImportHistory
    {
        // Créer l'entrée d'historique
        $importHistory = $this->createImportHistory($file, $workspace);

        try {
            // Traiter le fichier
            $this->processFileData($file, $importHistory, $workspace);
            
            // Marquer comme terminé
            $this->importHistoryRepository->update($importHistory, [
                'status' => 'completed',
                'completed_at' => now(),
            ]);

        } catch (\Exception $e) {
            // Marquer comme échoué
            $this->importHistoryRepository->update($importHistory, [
                'status' => 'failed',
                'errors' => [$e->getMessage()],
                'completed_at' => now(),
            ]);

            throw $e;
        }

        return $importHistory->fresh();
    }

    private function createImportHistory(UploadedFile $file, ?Workspace $workspace = null): ImportHistory
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('imports', $fileName, 'public');

        return $this->importHistoryRepository->create([
            'filename' => $fileName,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'status' => 'processing',
            'started_at' => now(),
            'workspace_id' => $workspace->id ?? null,
        ]);
    }

    private function processFileData(UploadedFile $file, ImportHistory $importHistory, ?Workspace $workspace = null): void
    {
        $extension = $file->getClientOriginalExtension();
        
        if (in_array($extension, ['csv', 'xlsx', 'xls'])) {
            $this->processExcelFile($file, $importHistory, $workspace);
        } else {
            throw new \InvalidArgumentException('Type de fichier non supporté: ' . $extension);
        }
    }

    private function processExcelFile(UploadedFile $file, ImportHistory $importHistory, ?Workspace $workspace = null): void
    {
        // Obtenir les en-têtes
        $headings = Excel::toArray(new HeadingRowImport, $file)[0][0] ?? [];
        
        // Lire toutes les données
        $data = Excel::toArray([], $file)[0];
        
        // Supprimer la ligne d'en-tête
        array_shift($data);

        $totalRows = count($data);
        $successfulRows = 0;
        $failedRows = 0;
        $errors = [];

        // Mettre à jour le total des lignes
        $this->importHistoryRepository->update($importHistory, [
            'total_rows' => $totalRows,
        ]);

        $batchData = [];
        $batchSize = 1000;

        foreach ($data as $index => $row) {
            try {
                // Créer un tableau associatif avec les en-têtes
                $rowData = [];
                foreach ($headings as $i => $heading) {
                    $rowData[$heading] = $row[$i] ?? null;
                }

                // Supprimer les lignes vides
                if (empty(array_filter($rowData))) {
                    continue;
                }

                $rowData['workspace_id'] = $workspace->id ?? null;

                $batchData[] = [
                    'import_history_id' => $importHistory->id,
                    'data' => json_encode($rowData),
                    'row_hash' => md5(json_encode($rowData)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $successfulRows++;

                // Traitement par batch pour améliorer les performances
                if (count($batchData) >= $batchSize) {
                    $this->processBatch($batchData, $importHistory, $errors);
                    $batchData = [];
                }

            } catch (\Exception $e) {
                $failedRows++;
                $errors[] = "Ligne " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        // Traiter le dernier batch
        if (!empty($batchData)) {
            $this->processBatch($batchData, $importHistory, $errors);
        }

        // Mettre à jour les statistiques finales
        $this->importHistoryRepository->update($importHistory, [
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
            'errors' => $errors,
        ]);
    }

    private function processBatch(array $batchData, ImportHistory $importHistory, array &$errors): void
    {
        try {
            \DB::table('imported_data')->insert($batchData);
        } catch (\Exception $e) {
            // En cas d'erreur de batch, essayer d'insérer une par une
            foreach ($batchData as $data) {
                try {
                    \DB::table('imported_data')->insert($data);
                } catch (\Exception $individualError) {
                    $errors[] = "Erreur d'insertion: " . $individualError->getMessage();
                }
            }
        }
    }

    public function getUniqueColumns(?Workspace $workspace = null): array
    {
        return $this->importedDataRepository->getUniqueColumns($workspace);
    }

    public function exportData(array $filters = [], ?Workspace $workspace = null): array
    {
        $data = $this->importedDataRepository->exportData($filters, $workspace);
        
        $exportData = [];
        foreach ($data as $item) {
            $exportData[] = $item->data;
        }

        return $exportData;
    }
    }
}
