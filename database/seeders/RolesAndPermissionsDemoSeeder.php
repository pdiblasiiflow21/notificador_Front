<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsDemoSeeder extends Seeder
{
    private $adminRole;

    private $developerRole;

    private $operatorRole;

    /**
     *  Crea los roles del sistema y asigna permisos usuarios de prueba.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear roles
        $this->adminRole     = Role::updateOrCreate(['name' => 'administrador']);
        $this->developerRole = Role::updateOrCreate(['name' => 'programador']);
        $this->operatorRole  = Role::updateOrCreate(['name' => 'operario']);

        $this->createNewSanPermissions();

        $admin = \App\Models\User::factory()->create([
            'name'  => 'Example Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole($this->adminRole);

        $developer = \App\Models\User::factory()->create([
            'name'  => 'Developer User',
            'email' => 'developer@example.com',
        ]);
        $developer->assignRole($this->developerRole);

        $operator = \App\Models\User::factory()->create([
            'name'  => 'Operator User',
            'email' => 'operator@example.com',
        ]);
        $operator->assignRole($this->operatorRole);
    }

    protected function createNewSanPermissions()
    {
        $permissions = ['ver_notificaciones', 'correr_notificaciones', 'descargar_notificaciones'];
        $this->createPermissionsForModule('NewSan', $permissions);
    }

    protected function createPermissionsForModule(string $module, array $actions)
    {
        foreach ($actions as $action) {
            // Creo el permiso para el modulo
            $permission = Permission::updateOrCreate(['name' => "$module-$action"]);

            // Asigno todos los permisos del modulo al rol administrador
            $this->adminRole->givePermissionTo($permission);

            if ($module === 'NewSan') {
                if ($action !== 'correr_notificaciones') {
                    // Asigno todos los permisos excepto 'correr_notificaciones' al rol programador
                    $this->developerRole->givePermissionTo($permission);
                }

                if ($action === 'ver_notificaciones') {
                    // Asigno solo el permiso 'ver_notificaciones' al rol operador
                    $this->operatorRole->givePermissionTo($permission);
                }
            }
        }
    }
}
