<?php

namespace Tests\Feature;

use App\Enums\Permiso;
use App\Enums\Rol;
use App\Models\Carrera;
use App\Models\Estudiante;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AutorizacionEstudiantesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisoVer = Permission::create([
            'name' => Permiso::Ver->value,
            'guard_name' => 'web',
        ]);

        $permisoCrear = Permission::create([
            'name' => Permiso::Crear->value,
            'guard_name' => 'web',
        ]);

        $permisoEditar = Permission::create([
            'name' => Permiso::Editar->value,
            'guard_name' => 'web',
        ]);

        $permisoEliminar = Permission::create([
            'name' => Permiso::Eliminar->value,
            'guard_name' => 'web',
        ]);

        $rolAdmin = Role::create([
            'name' => Rol::Administrador->value,
            'guard_name' => 'web',
        ]);

        $rolProfesor = Role::create([
            'name' => Rol::Profesor->value,
            'guard_name' => 'web',
        ]);

        $rolEstudiante = Role::create([
            'name' => Rol::Estudiante->value,
            'guard_name' => 'web',
        ]);

        $rolCoordinador = Role::create([
            'name' => Rol::Coordinador->value,
            'guard_name' => 'web',
        ]);

        $rolAdmin->givePermissionTo([
            $permisoVer,
            $permisoCrear,
            $permisoEditar,
            $permisoEliminar,
        ]);

        $rolProfesor->givePermissionTo([
            $permisoVer,
            $permisoEditar,
        ]);

        $rolEstudiante->givePermissionTo([
            $permisoVer,
        ]);

        $rolCoordinador->givePermissionTo([
            $permisoVer,
            $permisoEditar,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function crearSede(string $nombre = 'Sede Central', string $codigo = 'SC'): Sede
    {
        return Sede::create([
            'nombre' => $nombre,
            'codigo' => $codigo,
        ]);
    }

    private function crearCarrera(Sede $sede, string $nombre, string $codigo): Carrera
    {
        return Carrera::create([
            'nombre' => $nombre,
            'codigo' => $codigo,
            'sede_id' => $sede->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // 1. Pruebas base del enunciado (Sección 30)
    // -------------------------------------------------------------------------

    public function test_student_can_view_own_record(): void
    {
        $sede = $this->crearSede();
        $carrera = $this->crearCarrera($sede, 'Ingeniería de Software', 'ISW');

        $usuario = User::factory()->create([
            'carrera_id' => $carrera->id,
            'sede_id' => $sede->id,
        ]);
        $usuario->assignRole(Rol::Estudiante->value);

        $estudiante = Estudiante::create([
            'user_id' => $usuario->id,
            'carrera_id' => $carrera->id,
            'cedula' => '1-1111-1111',
            'nombre' => 'Ana',
            'apellido' => 'Prueba',
            'correo_institucional' => 'ana@test.test',
            'activo' => true,
        ]);

        $this->actingAs($usuario, 'sanctum')
            ->getJson("/api/estudiantes/{$estudiante->id}")
            ->assertOk();
    }

    public function test_student_cannot_view_another_student_record(): void
    {
        $sede = $this->crearSede();
        $carrera = $this->crearCarrera($sede, 'Ingeniería de Software', 'ISW');

        $usuario1 = User::factory()->create([
            'carrera_id' => $carrera->id,
            'sede_id' => $sede->id,
        ]);
        $usuario1->assignRole(Rol::Estudiante->value);

        $usuario2 = User::factory()->create([
            'carrera_id' => $carrera->id,
            'sede_id' => $sede->id,
        ]);
        $usuario2->assignRole(Rol::Estudiante->value);

        $estudiante = Estudiante::create([
            'user_id' => $usuario2->id,
            'carrera_id' => $carrera->id,
            'cedula' => '2-2222-2222',
            'nombre' => 'Luis',
            'apellido' => 'Prueba',
            'correo_institucional' => 'luis@test.test',
            'activo' => true,
        ]);

        $this->actingAs($usuario1, 'sanctum')
            ->getJson("/api/estudiantes/{$estudiante->id}")
            ->assertForbidden();
    }

    public function test_professor_cannot_update_student_from_another_career(): void
    {
        $sede = $this->crearSede();
        $carreraIti = $this->crearCarrera($sede, 'Tecnologías de Información', 'ITI');
        $carreraIsw = $this->crearCarrera($sede, 'Ingeniería de Software', 'ISW');

        $profesor = User::factory()->create([
            'carrera_id' => $carreraIti->id,
            'sede_id' => $sede->id,
        ]);
        $profesor->assignRole(Rol::Profesor->value);

        $propietario = User::factory()->create([
            'carrera_id' => $carreraIsw->id,
            'sede_id' => $sede->id,
        ]);
        $propietario->assignRole(Rol::Estudiante->value);

        $estudiante = Estudiante::create([
            'user_id' => $propietario->id,
            'carrera_id' => $carreraIsw->id,
            'cedula' => '3-3333-3333',
            'nombre' => 'Carlos',
            'apellido' => 'Prueba',
            'correo_institucional' => 'carlos@test.test',
            'activo' => true,
        ]);

        $this->actingAs($profesor, 'sanctum')
            ->putJson("/api/estudiantes/{$estudiante->id}", [
                'nombre' => 'Nombre modificado',
            ])
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // 2. Gates de Dashboards (Sección 13-14 y Actividad 1)
    // -------------------------------------------------------------------------

    public function test_admin_dashboard_gate_allows_only_administrators(): void
    {
        $sede = $this->crearSede();
        $carrera = $this->crearCarrera($sede, 'Tecnologías de Información', 'ITI');

        $admin = User::factory()->create(['carrera_id' => null, 'sede_id' => null]);
        $admin->assignRole(Rol::Administrador->value);

        $profesor = User::factory()->create(['carrera_id' => $carrera->id, 'sede_id' => $sede->id]);
        $profesor->assignRole(Rol::Profesor->value);

        $estudiante = User::factory()->create(['carrera_id' => $carrera->id, 'sede_id' => $sede->id]);
        $estudiante->assignRole(Rol::Estudiante->value);

        $coordinador = User::factory()->create(['carrera_id' => $carrera->id, 'sede_id' => $sede->id]);
        $coordinador->assignRole(Rol::Coordinador->value);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/dashboard')
            ->assertOk();

        $this->actingAs($profesor, 'sanctum')
            ->getJson('/api/admin/dashboard')
            ->assertForbidden();

        $this->actingAs($estudiante, 'sanctum')
            ->getJson('/api/admin/dashboard')
            ->assertForbidden();

        $this->actingAs($coordinador, 'sanctum')
            ->getJson('/api/admin/dashboard')
            ->assertForbidden();
    }

    public function test_professor_dashboard_gate_allows_only_professors(): void
    {
        $sede = $this->crearSede();
        $carrera = $this->crearCarrera($sede, 'Tecnologías de Información', 'ITI');

        $admin = User::factory()->create(['carrera_id' => null, 'sede_id' => null]);
        $admin->assignRole(Rol::Administrador->value);

        $profesor = User::factory()->create(['carrera_id' => $carrera->id, 'sede_id' => $sede->id]);
        $profesor->assignRole(Rol::Profesor->value);

        $estudiante = User::factory()->create(['carrera_id' => $carrera->id, 'sede_id' => $sede->id]);
        $estudiante->assignRole(Rol::Estudiante->value);

        $coordinador = User::factory()->create(['carrera_id' => $carrera->id, 'sede_id' => $sede->id]);
        $coordinador->assignRole(Rol::Coordinador->value);

        $this->actingAs($profesor, 'sanctum')
            ->getJson('/api/profesor/dashboard')
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/profesor/dashboard')
            ->assertForbidden();

        $this->actingAs($estudiante, 'sanctum')
            ->getJson('/api/profesor/dashboard')
            ->assertForbidden();

        $this->actingAs($coordinador, 'sanctum')
            ->getJson('/api/profesor/dashboard')
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // 3. Autenticación 401 sin token (Sección 28)
    // -------------------------------------------------------------------------

    public function test_unauthenticated_requests_to_auth_and_dashboards_return_401(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->postJson('/api/logout')->assertUnauthorized();
        $this->getJson('/api/admin/dashboard')->assertUnauthorized();
        $this->getJson('/api/profesor/dashboard')->assertUnauthorized();
    }

    public function test_unauthenticated_requests_to_estudiantes_return_401(): void
    {
        $this->getJson('/api/estudiantes')->assertUnauthorized();
        $this->postJson('/api/estudiantes', [])->assertUnauthorized();
    }

    public function test_public_register_ignores_roles_and_permissions_payload(): void
    {
        $respuesta = $this->postJson('/api/register', [
            'name' => 'Usuario Externo',
            'email' => 'externo@academica.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'rol' => Rol::Administrador->value,
            'roles' => [Rol::Administrador->value],
            'permissions' => [Permiso::Crear->value, Permiso::Eliminar->value],
        ]);

        $respuesta->assertCreated();

        $usuarioRegistrado = User::where('email', 'externo@academica.test')->firstOrFail();

        $this->assertFalse($usuarioRegistrado->hasRole(Rol::Administrador->value));
        $this->assertCount(0, $usuarioRegistrado->getRoleNames());
        $this->assertCount(0, $usuarioRegistrado->getAllPermissions());
    }

    // -------------------------------------------------------------------------
    // 4. Verificación de middleware RBAC en store (Sección 20)
    // -------------------------------------------------------------------------

    public function test_store_estudiante_route_has_permission_middleware(): void
    {
        $rutaStore = collect(Route::getRoutes()->getRoutes())->first(function ($ruta) {
            return $ruta->uri() === 'api/estudiantes' && in_array('POST', $ruta->methods(), true);
        });

        $this->assertNotNull(
            $rutaStore,
            'La ruta POST api/estudiantes no está registrada en routes/api/estudiantes.php (reportar a P4).'
        );

        $middlewares = $rutaStore->gatherMiddleware();

        $this->assertContains(
            'permission:'.Permiso::Crear->value,
            $middlewares,
            'La ruta POST api/estudiantes debe protegerse con el middleware permission:estudiantes.crear.'
        );
    }

    public function test_only_administrator_can_create_and_delete_student(): void
    {
        $sede = $this->crearSede();
        $carrera = $this->crearCarrera($sede, 'Tecnologías de Información', 'ITI');

        $admin = User::factory()->create(['carrera_id' => null, 'sede_id' => null]);
        $admin->assignRole(Rol::Administrador->value);

        $profesor = User::factory()->create(['carrera_id' => $carrera->id, 'sede_id' => $sede->id]);
        $profesor->assignRole(Rol::Profesor->value);

        $usuarioDestino = User::factory()->create(['carrera_id' => $carrera->id, 'sede_id' => $sede->id]);
        $usuarioDestino->assignRole(Rol::Estudiante->value);

        $cargaEstudiante = [
            'user_id' => $usuarioDestino->id,
            'carrera_id' => $carrera->id,
            'cedula' => '4-4444-4444',
            'nombre' => 'Sofía',
            'apellido' => 'Vargas',
            'correo_institucional' => 'sofia@estudiantes.test',
            'activo' => true,
        ];

        $this->actingAs($profesor, 'sanctum')
            ->postJson('/api/estudiantes', $cargaEstudiante)
            ->assertForbidden();

        $respuestaCreacion = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/estudiantes', $cargaEstudiante)
            ->assertCreated();

        $estudianteId = $respuestaCreacion->json('id');

        $this->actingAs($profesor, 'sanctum')
            ->deleteJson("/api/estudiantes/{$estudianteId}")
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/estudiantes/{$estudianteId}")
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // 5. Rol Coordinador (Actividad 3 - RBAC + ABAC)
    // -------------------------------------------------------------------------

    public function test_coordinator_has_rbac_permissions_and_respects_abac_rules(): void
    {
        $sedeCentral = $this->crearSede('Sede Central', 'SC');
        $carreraIti = $this->crearCarrera($sedeCentral, 'Tecnologías de Información', 'ITI');
        $carreraIsw = $this->crearCarrera($sedeCentral, 'Ingeniería de Software', 'ISW');

        $coordinador = User::factory()->create([
            'carrera_id' => $carreraIti->id,
            'sede_id' => $sedeCentral->id,
        ]);
        $coordinador->assignRole(Rol::Coordinador->value);

        $this->assertTrue($coordinador->can(Permiso::Ver->value));
        $this->assertTrue($coordinador->can(Permiso::Editar->value));
        $this->assertFalse($coordinador->can(Permiso::Crear->value));
        $this->assertFalse($coordinador->can(Permiso::Eliminar->value));

        $usuarioIti = User::factory()->create(['carrera_id' => $carreraIti->id, 'sede_id' => $sedeCentral->id]);
        $usuarioIsw = User::factory()->create(['carrera_id' => $carreraIsw->id, 'sede_id' => $sedeCentral->id]);

        $estudianteIti = Estudiante::create([
            'user_id' => $usuarioIti->id,
            'carrera_id' => $carreraIti->id,
            'cedula' => '5-5555-5555',
            'nombre' => 'Diego',
            'apellido' => 'Rojas',
            'correo_institucional' => 'diego@estudiantes.test',
            'activo' => true,
        ]);

        $estudianteIsw = Estudiante::create([
            'user_id' => $usuarioIsw->id,
            'carrera_id' => $carreraIsw->id,
            'cedula' => '6-6666-6666',
            'nombre' => 'Elena',
            'apellido' => 'Mora',
            'correo_institucional' => 'elena@estudiantes.test',
            'activo' => true,
        ]);

        // Misma carrera y sede: permitido actualizar
        $this->actingAs($coordinador, 'sanctum')
            ->putJson("/api/estudiantes/{$estudianteIti->id}", ['nombre' => 'Diego Actualizado'])
            ->assertOk();

        // Carrera distinta: denegado por ABAC
        $this->actingAs($coordinador, 'sanctum')
            ->putJson("/api/estudiantes/{$estudianteIsw->id}", ['nombre' => 'No Permitido'])
            ->assertForbidden();

        // Sin permiso de eliminación
        $this->actingAs($coordinador, 'sanctum')
            ->deleteJson("/api/estudiantes/{$estudianteIti->id}")
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // 6. ABAC por Carrera y Sede (Actividad 4 - 4 escenarios)
    // -------------------------------------------------------------------------

    public function test_professor_can_update_student_with_same_career_and_same_sede(): void
    {
        $sedeCentral = $this->crearSede('Sede Central', 'SC');
        $carreraIti = $this->crearCarrera($sedeCentral, 'Tecnologías de Información', 'ITI');

        $profesor = User::factory()->create([
            'carrera_id' => $carreraIti->id,
            'sede_id' => $sedeCentral->id,
        ]);
        $profesor->assignRole(Rol::Profesor->value);

        $usuarioEstudiante = User::factory()->create([
            'carrera_id' => $carreraIti->id,
            'sede_id' => $sedeCentral->id,
        ]);
        $usuarioEstudiante->assignRole(Rol::Estudiante->value);

        $estudiante = Estudiante::create([
            'user_id' => $usuarioEstudiante->id,
            'carrera_id' => $carreraIti->id,
            'cedula' => '7-7777-7777',
            'nombre' => 'Ana',
            'apellido' => 'Solís',
            'correo_institucional' => 'ana.solis@estudiantes.test',
            'activo' => true,
        ]);

        $this->actingAs($profesor, 'sanctum')
            ->putJson("/api/estudiantes/{$estudiante->id}", [
                'nombre' => 'Ana María',
            ])
            ->assertOk();
    }

    public function test_professor_cannot_update_student_with_same_career_and_different_sede(): void
    {
        $sedeCentral = $this->crearSede('Sede Central', 'SC');
        $sedeRegional = $this->crearSede('Sede Regional', 'SR');

        $carreraItiCentral = $this->crearCarrera($sedeCentral, 'Tecnologías de Información', 'ITI');

        // Profesor asignado a la misma carrera_id pero con sede_id diferente
        $profesorRegional = User::factory()->create([
            'carrera_id' => $carreraItiCentral->id,
            'sede_id' => $sedeRegional->id,
        ]);
        $profesorRegional->assignRole(Rol::Profesor->value);

        $usuarioEstudiante = User::factory()->create([
            'carrera_id' => $carreraItiCentral->id,
            'sede_id' => $sedeCentral->id,
        ]);
        $usuarioEstudiante->assignRole(Rol::Estudiante->value);

        $estudiante = Estudiante::create([
            'user_id' => $usuarioEstudiante->id,
            'carrera_id' => $carreraItiCentral->id,
            'cedula' => '8-8888-8888',
            'nombre' => 'Valeria',
            'apellido' => 'Campos',
            'correo_institucional' => 'valeria@estudiantes.test',
            'activo' => true,
        ]);

        $this->actingAs($profesorRegional, 'sanctum')
            ->putJson("/api/estudiantes/{$estudiante->id}", [
                'nombre' => 'Cambio No Autorizado',
            ])
            ->assertForbidden();
    }

    public function test_professor_cannot_update_student_with_different_career_and_different_sede(): void
    {
        $sedeCentral = $this->crearSede('Sede Central', 'SC');
        $sedeRegional = $this->crearSede('Sede Regional', 'SR');

        $carreraItiCentral = $this->crearCarrera($sedeCentral, 'Tecnologías de Información', 'ITI');
        $carreraIswRegional = $this->crearCarrera($sedeRegional, 'Ingeniería de Software Regional', 'ISW-SR');

        $profesor = User::factory()->create([
            'carrera_id' => $carreraItiCentral->id,
            'sede_id' => $sedeCentral->id,
        ]);
        $profesor->assignRole(Rol::Profesor->value);

        $usuarioEstudiante = User::factory()->create([
            'carrera_id' => $carreraIswRegional->id,
            'sede_id' => $sedeRegional->id,
        ]);
        $usuarioEstudiante->assignRole(Rol::Estudiante->value);

        $estudiante = Estudiante::create([
            'user_id' => $usuarioEstudiante->id,
            'carrera_id' => $carreraIswRegional->id,
            'cedula' => '9-9999-9999',
            'nombre' => 'Mateo',
            'apellido' => 'Jiménez',
            'correo_institucional' => 'mateo@estudiantes.test',
            'activo' => true,
        ]);

        $this->actingAs($profesor, 'sanctum')
            ->putJson("/api/estudiantes/{$estudiante->id}", [
                'nombre' => 'Cambio No Autorizado',
            ])
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // 7. Registro inactivo (Actividad 2 - Policies)
    // -------------------------------------------------------------------------

    public function test_inactive_student_record_cannot_be_viewed_by_other_student_professor_or_coordinator(): void
    {
        $sedeCentral = $this->crearSede('Sede Central', 'SC');
        $carreraIti = $this->crearCarrera($sedeCentral, 'Tecnologías de Información', 'ITI');

        $profesor = User::factory()->create([
            'carrera_id' => $carreraIti->id,
            'sede_id' => $sedeCentral->id,
        ]);
        $profesor->assignRole(Rol::Profesor->value);

        $coordinador = User::factory()->create([
            'carrera_id' => $carreraIti->id,
            'sede_id' => $sedeCentral->id,
        ]);
        $coordinador->assignRole(Rol::Coordinador->value);

        $otroEstudiante = User::factory()->create([
            'carrera_id' => $carreraIti->id,
            'sede_id' => $sedeCentral->id,
        ]);
        $otroEstudiante->assignRole(Rol::Estudiante->value);

        $usuarioInactivo = User::factory()->create([
            'carrera_id' => $carreraIti->id,
            'sede_id' => $sedeCentral->id,
        ]);
        $usuarioInactivo->assignRole(Rol::Estudiante->value);

        $estudianteInactivo = Estudiante::create([
            'user_id' => $usuarioInactivo->id,
            'carrera_id' => $carreraIti->id,
            'cedula' => '1-0000-0000',
            'nombre' => 'Pedro',
            'apellido' => 'Inactivo',
            'correo_institucional' => 'pedro.inactivo@estudiantes.test',
            'activo' => false,
        ]);

        $this->actingAs($otroEstudiante, 'sanctum')
            ->getJson("/api/estudiantes/{$estudianteInactivo->id}")
            ->assertForbidden();

        $this->actingAs($profesor, 'sanctum')
            ->getJson("/api/estudiantes/{$estudianteInactivo->id}")
            ->assertForbidden();

        $this->actingAs($coordinador, 'sanctum')
            ->getJson("/api/estudiantes/{$estudianteInactivo->id}")
            ->assertForbidden();
    }
}
