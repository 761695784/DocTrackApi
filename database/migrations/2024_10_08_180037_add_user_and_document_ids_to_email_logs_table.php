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
        Schema::table('email_logs', function (Blueprint $table) {
            // Ajout des colonnes
            $table->foreignId('publisher_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('requester_user_id')->nullable()->constrained('users')->onDelete('cascade');
            // $table->foreignId('document_id')->nullable()->constrained('documents')->onDelete('cascade');
            $table->foreignId('declarant_user_id')->nullable()->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            // Suppression des colonnes si on fait un rollback
            $table->dropForeign(['publisher_user_id']);
            $table->dropColumn('publisher_user_id');

            $table->dropForeign(['requester_user_id']);
            $table->dropColumn('requester_user_id');

            // $table->dropForeign(['document_id']);
            // $table->dropColumn('document_id');

            $table->dropForeign(['declarant_user_id']);
            $table->dropColumn('declarant_user_id');
        });
    }
};
