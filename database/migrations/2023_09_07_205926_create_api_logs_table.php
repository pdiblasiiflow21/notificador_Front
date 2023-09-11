<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    private $tableName = 'api_logs';

    public function up(): void
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string('request_endpoint');
            $table->string('request_method');
            $table->text('request_credentials')->nullable();
            $table->string('response_status_code');
            $table->longText('response')->nullable();
            $table->double('response_time', 15, 8)->nullable()->comment('Tiempo de respuesta en microseconds');
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `$this->tableName` comment 'Tabla que guarda datos de los pedidos y respuestas a las apis externas al sistema.'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName);
    }
};
