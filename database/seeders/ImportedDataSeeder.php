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
        // Créer quelques historiques d'import
        $importHistory1 = \App\Models\ImportHistory::create([
            'filename' => 'employees_2025.csv',
            'original_filename' => 'employees_2025.csv',
            'file_path' => 'imports/employees_2025.csv',
            'file_type' => 'csv',
            'status' => 'completed',
            'total_rows' => 100,
            'successful_rows' => 98,
            'failed_rows' => 2,
            'errors' => ['Ligne 15: Format email invalide', 'Ligne 67: Âge manquant'],
            'started_at' => now()->subDays(2),
            'completed_at' => now()->subDays(2)->addMinutes(5),
        ]);

        $importHistory2 = \App\Models\ImportHistory::create([
            'filename' => 'products_2025.xlsx',
            'original_filename' => 'products_2025.xlsx',
            'file_path' => 'imports/products_2025.xlsx',
            'file_type' => 'xlsx',
            'status' => 'completed',
            'total_rows' => 50,
            'successful_rows' => 50,
            'failed_rows' => 0,
            'errors' => null,
            'started_at' => now()->subDay(),
            'completed_at' => now()->subDay()->addMinutes(2),
        ]);

        // Générer des données d'employés
        for ($i = 1; $i <= 98; $i++) {
            \App\Models\ImportedData::create([
                'import_history_id' => $importHistory1->id,
                'data' => [
                    'nom' => fake()->lastName(),
                    'prenom' => fake()->firstName(),
                    'email' => fake()->unique()->safeEmail(),
                    'age' => fake()->numberBetween(22, 65),
                    'departement' => fake()->randomElement(['IT', 'RH', 'Finance', 'Marketing', 'Ventes']),
                    'salaire' => fake()->numberBetween(30000, 120000),
                    'date_embauche' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                    'statut' => fake()->randomElement(['Actif', 'Inactif', 'En congé']),
                ],
                'row_hash' => md5("employee_{$i}"),
            ]);
        }

        // Générer des données de produits
        for ($i = 1; $i <= 50; $i++) {
            \App\Models\ImportedData::create([
                'import_history_id' => $importHistory2->id,
                'data' => [
                    'nom_produit' => fake()->words(3, true),
                    'sku' => fake()->unique()->regexify('[A-Z]{3}-[0-9]{4}'),
                    'prix' => fake()->randomFloat(2, 10, 1000),
                    'stock' => fake()->numberBetween(0, 500),
                    'categorie' => fake()->randomElement(['Électronique', 'Vêtements', 'Maison', 'Sport', 'Livres']),
                    'fournisseur' => fake()->company(),
                    'date_creation' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                    'actif' => fake()->boolean(80),
                ],
                'row_hash' => md5("product_{$i}"),
            ]);
        }
    }
}
