<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->string('phase', 30); // planning, design, dll
            $table->string('action', 50)->nullable();
            $table->string('title');
            $table->text('description')->nullable();

            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['project_id', 'phase']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_activities');
    }
};