<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            $table->string('pic', 100)->nullable()->after('status');
            $table->timestamp('start_at')->nullable()->after('pic');
            $table->timestamp('end_at')->nullable()->after('start_at');
        });
    }

    public function down(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            $table->dropColumn(['pic','start_at','end_at']);
        });
    }
};
