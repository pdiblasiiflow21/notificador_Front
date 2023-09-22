<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    private $tableName = 'NewSan_notification_logs';

    public function up(): void
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string('message');
            $table->json('notified')->nullable();
            $table->json('finalized')->nullable();
            $table->double('response_time', 15, 8)->nullable()->comment('Tiempo de respuesta en microseconds');
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `$this->tableName` comment 'Tabla que guarda el resultado del proceso de notificacion de ordenes a la api de NewSan'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName);
    }
};
