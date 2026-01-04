<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            if (!Schema::hasColumn('requirements', 'pic')) {
                $table->string('pic')->nullable()->after('status');
            }

            if (!Schema::hasColumn('requirements', 'start_date')) {
                $table->date('start_date')->nullable()->after('pic');
            }

            if (!Schema::hasColumn('requirements', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->dropColumn(['pic','start_date','end_date']);
        });
    }
};
