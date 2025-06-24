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
        Schema::table('import_histories', function (Blueprint $table) {
            // Modifier l'énumération pour ajouter 'rolled_back'
            $table->dropColumn('status');
        });
        
        Schema::table('import_histories', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'rolled_back'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_histories', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('import_histories', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
        });
    }
};
