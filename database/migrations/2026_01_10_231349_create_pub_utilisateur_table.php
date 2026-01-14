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
        Schema::create('pub_utilisateur', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('pub_id');
            $table->uuid('utilisateur_id');

            $table->foreign('pub_id')
                ->references('id')
                ->on('pubs')
                ->onDelete('cascade');

            $table->foreign('utilisateur_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pub_utilisateur');
    }
};
