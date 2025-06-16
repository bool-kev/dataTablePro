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
        Schema::create('imported_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_history_id')->constrained()->onDelete('cascade');
            $table->json('data');
            $table->string('row_hash')->unique(); // Pour éviter les doublons
            $table->timestamps();
            
            $table->index(['import_history_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imported_data');
    }
};
