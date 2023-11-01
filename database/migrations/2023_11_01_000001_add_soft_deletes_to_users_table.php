<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    private $tableName = 'users';

    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
