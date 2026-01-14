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
        Schema::create('avis', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('trajet_id');
            $table->uuid('utilisateur_id');

            $table->integer('note');
            $table->text('commentaire')->nullable();

            $table->foreign('trajet_id')
                ->references('id')
                ->on('trajets')
                ->onDelete('cascade');

            $table->foreign('utilisateur_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avis');
    }
};
