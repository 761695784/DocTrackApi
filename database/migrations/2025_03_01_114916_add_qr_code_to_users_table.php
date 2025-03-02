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
        Schema::table('users', function (Blueprint $table) {
            $table->string('qr_code_token')->nullable(); // Token unique pour le QR code
            $table->timestamp('qr_code_expires_at')->nullable(); // Date d’expiration du QR code
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['qr_code_token', 'qr_code_expires_at']);
        });
    }
};
