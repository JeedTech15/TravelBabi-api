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
        Schema::create('souscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('utilisateur_id');
            $table->uuid('abonnement_id');

            $table->foreign('utilisateur_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('abonnement_id')
                ->references('id')
                ->on('abonnements')
                ->cascadeOnDelete();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('souscriptions');
    }
};
