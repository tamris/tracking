<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deployments')) {
            return;
        }

        Schema::create('deployments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('environment')    // Staging / Production / lainnya
                  ->default('Staging');

            $table->string('version')->nullable();  // contoh: v1.0.0
            $table->dateTime('deployed_at')->nullable();

            $table->enum('status', ['Planned','In Progress','Success','Failed'])
                  ->default('Planned');

            $table->string('url')->nullable();   // link aplikasi (opsional)
            $table->text('notes')->nullable();   // catatan deployment

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};
  