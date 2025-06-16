<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            // Supprimer d'abord les index/contraintes
            $table->dropUnique(['database_name']);
            
            // Ensuite supprimer les colonnes liées aux bases de données multiples
            $table->dropColumn([
                'database_name',
                'database_path', 
                'database_type',
                'database_config'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            // Restaurer les colonnes en cas de rollback
            $table->string('database_name')->unique()->nullable();
            $table->string('database_path')->nullable();
            $table->enum('database_type', ['sqlite', 'mysql', 'postgresql'])->default('sqlite');
            $table->json('database_config')->nullable();
        });
    }
};
