<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
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
        // $this->operatorRole  = Role::updateOrCreate(['name' => 'operario']);

        $this->createGeneralPermissions();
        $this->createNewSanPermissions();

        $admin = User::updateOrCreate([
            'name'     => 'Example Admin User',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'email'    => 'admin@example.com',
        ]);
        $admin->assignRole($this->adminRole);

        $developer = User::updateOrCreate([
            'name'     => 'Developer User',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'email'    => 'developer@example.com',
        ]);
        $developer->assignRole($this->developerRole);

        // $operator = User::updateOrCreate([
        //     'name'     => 'Operator User',
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'email'    => 'operator@example.com',
        // ]);
        // $operator->assignRole($this->operatorRole);
    }

    protected function createGeneralPermissions()
    {
        $permissions = ['administrar_usuarios_y_permisos'];
        $this->createPermissionsForModule('General', $permissions);
    }

    protected function createNewSanPermissions()
    {
        $permissions = ['ver_notificaciones', 'descargar_notificaciones'];
        $this->createPermissionsForModule('NewSan', $permissions);
    }

    protected function createPermissionsForModule(string $module, array $actions)
    {
        foreach ($actions as $action) {
            // Creo el permiso para el modulo
            $permission = Permission::updateOrCreate(['name' => "$module.$action"]);

            // Asigno todos los permisos del modulo al rol administrador
            $this->adminRole->givePermissionTo($permission);

            if ($module === 'NewSan') {
                if ($action !== 'correr_notificaciones') {
                    // Asigno todos los permisos excepto 'correr_notificaciones' al rol programador
                    $this->developerRole->givePermissionTo($permission);
                }

                // if ($action === 'ver_notificaciones') {
                //     // Asigno solo el permiso 'ver_notificaciones' al rol operador
                //     $this->operatorRole->givePermissionTo($permission);
                // }
            }
        }
    }
}
