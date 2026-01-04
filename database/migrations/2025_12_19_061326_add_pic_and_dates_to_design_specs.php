<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('design_specs', function (Blueprint $table) {
            $table->string('pic', 120)->nullable()->after('status');
            $table->date('start_date')->nullable()->after('pic');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('design_specs', function (Blueprint $table) {
            $table->dropColumn(['pic', 'start_date', 'end_date']);
        });
    }
};
