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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); 
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete(); 
            $table->enum('status', ['pending', 'read', 'accepted', 'rejected'])->default('pending');
            $table->string('cv_path')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'offer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
