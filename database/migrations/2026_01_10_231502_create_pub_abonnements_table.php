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
            $table->id();
            $table->string('libelle');
            $table->text('description');
            $table->integer('prix');
            $table->integer('duree_validite');
            $table->boolean('populaire')->default(false);
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
