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
        Schema::create('pub_abonnements', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('pub_id');
            $table->uuid('abonnement_id');

            $table->foreign('pub_id')
                ->references('id')
                ->on('pubs')
                ->onDelete('cascade');

            $table->foreign('abonnement_id')
                ->references('id')
                ->on('abonnements')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pub_abonnements');
    }
};
