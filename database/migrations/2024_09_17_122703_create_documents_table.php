<?php

use App\Models\DocumentType;
use App\Models\User;
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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('OwnerFirstName')->nullable();
            $table->string('OwnerLastName')->nullable();
            $table->String('DocIdentification')->nullable();
            $table->string('Location');
            $table->enum('statut', ['récupéré', 'non récupéré'])->default('non récupéré');
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
        Schema::dropIfExists('documents');
    }
};
