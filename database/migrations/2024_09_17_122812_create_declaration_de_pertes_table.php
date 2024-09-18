<?php

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('declaration_de_pertes', function (Blueprint $table) {
            $table->id();
            $table->string('Title');
            $table->string('FirstNameInDoc');
            $table->string('LastNameInDoc');
            $table->string('DocIdentification')->nullable();
            $table->foreignIdFor(DocumentType::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('declaration_de_pertes');
    }
};
