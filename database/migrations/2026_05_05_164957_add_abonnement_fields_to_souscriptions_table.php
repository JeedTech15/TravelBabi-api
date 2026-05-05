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
        Schema::table('souscriptions', function (Blueprint $table) {
            $table->timestamp('creation_abonnement')->nullable()->after('abonnement_id');
            $table->timestamp('expire_abonnement')->nullable()->after('creation_abonnement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('souscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'creation_abonnement',
                'expire_abonnement'
            ]);

        });
    }
};
