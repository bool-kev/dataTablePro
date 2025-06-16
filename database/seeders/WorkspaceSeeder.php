<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();
        
        if (!$user) {
            return; // Pas d'utilisateur pour créer des workspaces
        }

        // Créer un workspace par défaut
        $defaultWorkspace = \App\Models\Workspace::create([
            'name' => 'Mon Workspace Principal',
            'description' => 'Workspace par défaut pour commencer',
            'slug' => 'main-workspace',
            'database_name' => 'workspace_main',
            'database_type' => 'sqlite',
            'owner_id' => $user->id,
        ]);

        // Créer la base de données et les tables
        $defaultWorkspace->createDatabase();
        $defaultWorkspace->setupDatabaseConnection();

        // Ajouter l'utilisateur au workspace
        $defaultWorkspace->users()->attach($user->id, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        // Créer un deuxième workspace de test
        $testWorkspace = \App\Models\Workspace::create([
            'name' => 'Workspace de Test',
            'description' => 'Workspace pour les tests et expérimentations',
            'slug' => 'test-workspace',
            'database_name' => 'workspace_test',
            'database_type' => 'sqlite',
            'owner_id' => $user->id,
        ]);

        $testWorkspace->createDatabase();
        $testWorkspace->setupDatabaseConnection();

        $testWorkspace->users()->attach($user->id, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        // Mettre le workspace principal comme actuel
        session(['current_workspace_id' => $defaultWorkspace->id]);
    }
}
