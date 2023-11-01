<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanTables extends Command
{
    protected $signature = 'run:clean-tables';

    protected $description = 'Limpia las tablas para usuarios, roles y permisos.';

    public function handle(): int
    {
        $this->info('Limpiando tablas...');

        // List of tables to clean
        $tables = [
            'model_has_roles',
            'role_has_permissions',
            'roles',
            'permissions',
            'users',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tables as $table) {
            DB::table($table)->truncate();
            $this->info("La tabla {$table} se limpió");
        }

        $this->info('Todas las tablas se limiaron.');

        return 0;
    }
}
