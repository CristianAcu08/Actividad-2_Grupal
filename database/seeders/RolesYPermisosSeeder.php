<?php

namespace Database\Seeders;

use App\Enums\Permiso;
use App\Enums\Rol;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesYPermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = [
            Permiso::Ver->value,
            Permiso::Crear->value,
            Permiso::Editar->value,
            Permiso::Eliminar->value,
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => Rol::Administrador->value,
            'guard_name' => 'web',
        ]);

        $profesor = Role::firstOrCreate([
            'name' => Rol::Profesor->value,
            'guard_name' => 'web',
        ]);

        $estudiante = Role::firstOrCreate([
            'name' => Rol::Estudiante->value,
            'guard_name' => 'web',
        ]);

        $coordinador = Role::firstOrCreate([
            'name' => Rol::Coordinador->value,
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions($permisos);

        $profesor->syncPermissions([
            Permiso::Ver->value,
            Permiso::Editar->value,
        ]);

        $estudiante->syncPermissions([
            Permiso::Ver->value,
        ]);

        $coordinador->syncPermissions([
            Permiso::Ver->value,
            Permiso::Editar->value,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
