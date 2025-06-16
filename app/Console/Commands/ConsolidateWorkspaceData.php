<?php

namespace App\Console\Commands;

use App\Models\Workspace;
use App\Models\ImportHistory;
use App\Models\ImportedData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConsolidateWorkspaceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workspace:consolidate-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consolidate data from individual workspace databases into the main database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting workspace data consolidation...');

        // Récupérer tous les workspaces
        $workspaces = Workspace::all();
        
        if ($workspaces->isEmpty()) {
            $this->info('No workspaces found.');
            return;
        }

        $totalConsolidated = 0;
        
        foreach ($workspaces as $workspace) {
            $this->info("Processing workspace: {$workspace->name} (ID: {$workspace->id})");
            
            $consolidated = $this->consolidateWorkspaceData($workspace);
            $totalConsolidated += $consolidated;
            
            $this->info("  → Consolidated {$consolidated} records");
        }

        $this->info("Consolidation completed! Total records consolidated: {$totalConsolidated}");
        
        // Nettoyer les anciens fichiers de base de données workspace
        $this->cleanupWorkspaceDatabases();
    }

    private function consolidateWorkspaceData(Workspace $workspace): int
    {
        $consolidated = 0;
        $workspaceDbPath = database_path("workspaces/{$workspace->database_name}.sqlite");
        
        // Vérifier si le fichier de base de données du workspace existe
        if (!file_exists($workspaceDbPath)) {
            $this->warn("  Database file not found: {$workspaceDbPath}");
            return 0;
        }

        try {
            // Configurer une connexion temporaire vers la base de données du workspace
            $connectionName = "temp_workspace_{$workspace->id}";
            config([
                "database.connections.{$connectionName}" => [
                    'driver' => 'sqlite',
                    'database' => $workspaceDbPath,
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ]
            ]);

            // Vérifier si les tables existent dans la base de données du workspace
            if (!Schema::connection($connectionName)->hasTable('import_histories')) {
                $this->warn("  No import_histories table found");
                return 0;
            }

            // Migrer les import_histories
            $importHistories = DB::connection($connectionName)
                ->table('import_histories')
                ->get();

            foreach ($importHistories as $history) {
                // Créer l'historique d'import dans la base principale
                $newHistory = ImportHistory::create([
                    'workspace_id' => $workspace->id,
                    'filename' => $history->filename,
                    'original_filename' => $history->original_filename,
                    'file_path' => $history->file_path,
                    'file_type' => $history->file_type,
                    'total_rows' => $history->total_rows,
                    'successful_rows' => $history->successful_rows,
                    'failed_rows' => $history->failed_rows,
                    'errors' => json_decode($history->errors),
                    'status' => $history->status,
                    'started_at' => $history->started_at,
                    'completed_at' => $history->completed_at,
                    'created_at' => $history->created_at,
                    'updated_at' => $history->updated_at,
                ]);

                // Migrer les données importées
                if (Schema::connection($connectionName)->hasTable('imported_data')) {
                    $importedData = DB::connection($connectionName)
                        ->table('imported_data')
                        ->where('import_history_id', $history->id)
                        ->get();

                    foreach ($importedData as $data) {
                        ImportedData::create([
                            'import_history_id' => $newHistory->id,
                            'data' => $data->data,
                            'row_hash' => $data->row_hash,
                            'created_at' => $data->created_at,
                            'updated_at' => $data->updated_at,
                        ]);
                        $consolidated++;
                    }
                }
            }

            // Purger la connexion temporaire
            DB::purge($connectionName);

        } catch (\Exception $e) {
            $this->error("  Error processing workspace {$workspace->id}: " . $e->getMessage());
        }

        return $consolidated;
    }

    private function cleanupWorkspaceDatabases(): void
    {
        $this->info('Cleaning up old workspace database files...');
        
        $workspaceDir = database_path('workspaces');
        
        if (!is_dir($workspaceDir)) {
            return;
        }

        $files = glob($workspaceDir . '/*.sqlite');
        $removedCount = 0;
        
        foreach ($files as $file) {
            if ($this->confirm("Remove workspace database file: " . basename($file) . "?", true)) {
                if (unlink($file)) {
                    $removedCount++;
                    $this->info("  → Removed: " . basename($file));
                }
            }
        }

        $this->info("Removed {$removedCount} workspace database files.");
    }
}
