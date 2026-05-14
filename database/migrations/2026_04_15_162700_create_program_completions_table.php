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
        Schema::create('program_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('completion_date');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->unique(['program_item_id', 'completion_date']);
            $table->index(['user_id', 'completion_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_completions');
    }
};
