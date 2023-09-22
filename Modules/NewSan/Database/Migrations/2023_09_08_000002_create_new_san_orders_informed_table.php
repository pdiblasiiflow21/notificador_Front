<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    private $tableName = 'NewSan_orders_informed';

    public function up(): void
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->bigInteger('api_id')->comment('Id informada por la api de iflow.')->primary();
            $table->string('order_id');
            $table->string('shipment_id');
            $table->string('tracking_id');
            $table->string('state_id');
            $table->string('state_name');
            $table->string('message');
            $table->string('state_date')->comment('Fecha informada por la api');
            $table->boolean('finalized')->default(false)->comment('Flag que indica si la orden ya no se tiene que notificar a la api de NewSan. Si esta en 1 no se debe notificar, si es 0, se debe notificar.');
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `$this->tableName` comment 'Tabla que guarda los datos de las ordenes enviadas a la api iflow para NewSan'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName);
    }
};
