<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('developments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->foreignId('design_spec_id')->constrained()->cascadeOnDelete();
            $t->string('developer')->nullable();
            $t->enum('status', ['In Progress','Review','Done'])->default('In Progress');
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('developments');
    }
};
