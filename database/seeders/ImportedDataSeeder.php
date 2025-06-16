<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ImportedDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer plusieurs historiques d'import avec différentes dates pour les graphiques
        $histories = [
            [
                'filename' => 'employees_2025.csv',
                'original_filename' => 'employees_2025.csv',
                'file_path' => 'imports/employees_2025.csv',
                'file_type' => 'csv',
                'status' => 'completed',
                'total_rows' => 100,
                'successful_rows' => 98,
                'failed_rows' => 2,
                'errors' => ['Ligne 15: Format email invalide', 'Ligne 67: Âge manquant'],
                'started_at' => now()->subDays(6),
                'completed_at' => now()->subDays(6)->addMinutes(5),
                'data_count' => 98
            ],
            [
                'filename' => 'products_2025.xlsx',
                'original_filename' => 'products_2025.xlsx',
                'file_path' => 'imports/products_2025.xlsx',
                'file_type' => 'xlsx',
                'status' => 'completed',
                'total_rows' => 50,
                'successful_rows' => 50,
                'failed_rows' => 0,
                'errors' => null,
                'started_at' => now()->subDays(4),
                'completed_at' => now()->subDays(4)->addMinutes(2),
                'data_count' => 50
            ],
            [
                'filename' => 'clients_export.csv',
                'original_filename' => 'clients_export.csv',
                'file_path' => 'imports/clients_export.csv',
                'file_type' => 'csv',
                'status' => 'completed',
                'total_rows' => 75,
                'successful_rows' => 73,
                'failed_rows' => 2,
                'errors' => ['Format téléphone invalide'],
                'started_at' => now()->subDays(3),
                'completed_at' => now()->subDays(3)->addMinutes(3),
                'data_count' => 73
            ],
            [
                'filename' => 'inventory.xlsx',
                'original_filename' => 'inventory.xlsx',
                'file_path' => 'imports/inventory.xlsx',
                'file_type' => 'xlsx',
                'status' => 'completed',
                'total_rows' => 200,
                'successful_rows' => 195,
                'failed_rows' => 5,
                'errors' => null,
                'started_at' => now()->subDays(1),
                'completed_at' => now()->subDays(1)->addMinutes(8),
                'data_count' => 195
            ],
            [
                'filename' => 'today_data.csv',
                'original_filename' => 'today_data.csv',
                'file_path' => 'imports/today_data.csv',
                'file_type' => 'csv',
                'status' => 'completed',
                'total_rows' => 30,
                'successful_rows' => 30,
                'failed_rows' => 0,
                'errors' => null,
                'started_at' => now()->subHours(2),
                'completed_at' => now()->subHours(2)->addMinutes(1),
                'data_count' => 30
            ]
        ];

        foreach ($histories as $historyData) {
            $dataCount = $historyData['data_count'];
            unset($historyData['data_count']);
            
            $importHistory = \App\Models\ImportHistory::create($historyData);

            // Générer des données pour cet import
            for ($i = 1; $i <= $dataCount; $i++) {
                if ($historyData['file_type'] === 'csv') {
                    \App\Models\ImportedData::create([
                        'import_history_id' => $importHistory->id,
                        'data' => [
                            'nom' => fake()->lastName(),
                            'prenom' => fake()->firstName(),
                            'email' => fake()->unique()->safeEmail(),
                            'age' => fake()->numberBetween(18, 65),
                            'departement' => fake()->randomElement(['IT', 'HR', 'Finance', 'Marketing']),
                            'salaire' => fake()->numberBetween(30000, 80000),
                        ],
                        'row_hash' => fake()->unique()->md5(),
                    ]);
                } else {
                    \App\Models\ImportedData::create([
                        'import_history_id' => $importHistory->id,
                        'data' => [
                            'nom_produit' => fake()->words(2, true),
                            'prix' => fake()->randomFloat(2, 10, 500),
                            'categorie' => fake()->randomElement(['Électronique', 'Vêtements', 'Livres', 'Sport']),
                            'stock' => fake()->numberBetween(0, 100),
                            'fournisseur' => fake()->company(),
                        ],
                        'row_hash' => fake()->unique()->md5(),
                    ]);
                }
            }
        }
    }
}
