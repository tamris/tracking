<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hindari error jika migration dijalankan dua kali
        if (Schema::hasTable('test_cases')) {
            return;
        }

        Schema::create('test_cases', function (Blueprint $table) {
            $table->id();

            // Relasi ke Project (WAJIB)
            $table->foreignId('project_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Relasi ke Requirement (OPSIONAL)
            $table->foreignId('requirement_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // Relasi ke DesignSpec (OPSIONAL)
            $table->foreignId('design_spec_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // Informasi Test Case
            $table->string('title');                     // Judul test case
            $table->text('scenario')->nullable();        // Steps / skenario
            $table->text('expected_result')->nullable(); // Expected result
            $table->string('tester')->nullable();        // Nama tester (opsional)

            // Status testing
            $table->enum('status', ['Planned','In Progress','Passed','Failed'])
                  ->default('Planned');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_cases');
    }
};
