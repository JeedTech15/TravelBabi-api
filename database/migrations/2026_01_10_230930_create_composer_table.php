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
        Schema::create('composer', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('itineraire_id');
            $table->uuid('noeud_id');

            $table->foreign('itineraire_id')
                ->references('id')
                ->on('itineraires')
                ->onDelete('cascade');

            $table->foreign('noeud_id')
                ->references('id')
                ->on('noeuds')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('composer');
    }
};
