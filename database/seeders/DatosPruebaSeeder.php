<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\Carrera;
use App\Models\Estudiante;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatosPruebaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sedeCentral = Sede::firstOrCreate(
            ['codigo' => 'SC'],
            ['nombre' => 'Sede Central']
        );

        $sedeRegional = Sede::firstOrCreate(
            ['codigo' => 'SR'],
            ['nombre' => 'Sede Regional']
        );

        $sistemas = Carrera::firstOrCreate(
            ['codigo' => 'ITI'],
            [
                'nombre' => 'Ingeniería en Tecnologías de Información',
                'sede_id' => $sedeCentral->id,
            ]
        );

        $software = Carrera::firstOrCreate(
            ['codigo' => 'ISW'],
            [
                'nombre' => 'Ingeniería de Software',
                'sede_id' => $sedeCentral->id,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@academica.test'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('Password123!'),
                'carrera_id' => null,
                'sede_id' => null,
            ]
        );
        $admin->syncRoles([Rol::Administrador->value]);

        $profesorITI = User::firstOrCreate(
            ['email' => 'profesor.iti@academica.test'],
            [
                'name' => 'Profesor de TI',
                'password' => Hash::make('Password123!'),
                'carrera_id' => $sistemas->id,
                'sede_id' => $sedeCentral->id,
            ]
        );
        $profesorITI->syncRoles([Rol::Profesor->value]);

        $profesorISW = User::firstOrCreate(
            ['email' => 'profesor.isw@academica.test'],
            [
                'name' => 'Profesor de Software',
                'password' => Hash::make('Password123!'),
                'carrera_id' => $software->id,
                'sede_id' => $sedeCentral->id,
            ]
        );
        $profesorISW->syncRoles([Rol::Profesor->value]);

        $usuarioEstudiante1 = User::firstOrCreate(
            ['email' => 'estudiante1@academica.test'],
            [
                'name' => 'Ana Estudiante',
                'password' => Hash::make('Password123!'),
                'carrera_id' => $sistemas->id,
                'sede_id' => $sedeCentral->id,
            ]
        );
        $usuarioEstudiante1->syncRoles([Rol::Estudiante->value]);

        $usuarioEstudiante2 = User::firstOrCreate(
            ['email' => 'estudiante2@academica.test'],
            [
                'name' => 'Luis Estudiante',
                'password' => Hash::make('Password123!'),
                'carrera_id' => $software->id,
                'sede_id' => $sedeCentral->id,
            ]
        );
        $usuarioEstudiante2->syncRoles([Rol::Estudiante->value]);

        $coordinador = User::firstOrCreate(
            ['email' => 'coordinador@academica.test'],
            [
                'name' => 'Coordinador Académico',
                'password' => Hash::make('Password123!'),
                'carrera_id' => $sistemas->id,
                'sede_id' => $sedeCentral->id,
            ]
        );
        $coordinador->syncRoles([Rol::Coordinador->value]);

        Estudiante::firstOrCreate(
            ['user_id' => $usuarioEstudiante1->id],
            [
                'carrera_id' => $sistemas->id,
                'cedula' => '1-1111-1111',
                'nombre' => 'Ana',
                'apellido' => 'Estudiante',
                'correo_institucional' => 'ana@estudiantes.test',
                'activo' => true,
            ]
        );

        Estudiante::firstOrCreate(
            ['user_id' => $usuarioEstudiante2->id],
            [
                'carrera_id' => $software->id,
                'cedula' => '2-2222-2222',
                'nombre' => 'Luis',
                'apellido' => 'Estudiante',
                'correo_institucional' => 'luis@estudiantes.test',
                'activo' => true,
            ]
        );
    }
}
