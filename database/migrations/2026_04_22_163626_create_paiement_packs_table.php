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
        Schema::create('paiement_packs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('data')->nullable();
            $table->integer('prix');
            $table->string('statut')->default('pending');
            $table->uuid('id_user');
            $table->foreign('id_user')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->uuid('id_pack');
            $table->foreign('id_pack')
                ->references('id')
                ->on('packs')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiement_packs');
    }
};
