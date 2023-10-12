<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    private $tableName = 'NewSan_orders_informed';

    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->string('last_notified_state', 255)->nullable()->after('state_date')->comment('Ultimo estado informado.');
        });
    }

    public function down(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('last_notified_state');
        });
    }
};
