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
        Schema::create('legal_documents', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->enum('type', ['privacy_policy','terms_conditions','cookies_policy']);
        $table->string('title');
        $table->longText('content');
        $table->string('version')->default('1.0');
        $table->boolean('is_active')->default(false);
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
