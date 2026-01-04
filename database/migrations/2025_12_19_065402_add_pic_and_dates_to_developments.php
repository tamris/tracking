<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('developments', function (Blueprint $t) {
            if (!Schema::hasColumn('developments','pic')) {
                $t->string('pic')->nullable()->after('design_spec_id');
            }
            if (!Schema::hasColumn('developments','start_date')) {
                $t->date('start_date')->nullable()->after('status');
            }
            if (!Schema::hasColumn('developments','end_date')) {
                $t->date('end_date')->nullable()->after('start_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('developments', function (Blueprint $t) {
            $t->dropColumn(['pic','start_date','end_date']);
        });
    }
};
