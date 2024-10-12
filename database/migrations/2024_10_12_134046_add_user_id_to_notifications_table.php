<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Ajoutez cette ligne pour créer la colonne user_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']); // Supprimez la contrainte de clé étrangère
            $table->dropColumn('user_id'); // Supprimez la colonne user_id
        });
    }
};
