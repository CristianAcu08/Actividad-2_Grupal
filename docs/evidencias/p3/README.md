# Evidencias de Entrega - P3: RBAC, Gates y Datos de Prueba

**Responsable:** Persona 3 (P3)  
**Rama de trabajo:** `p3-rbac-gates`  
**Módulos asignados:** Secciones 11–12, 13–14, 25, 29, 31 (Actividad 1: Gates) y 33 (Actividad 3: RBAC) del enunciado del taller.

---

## 1. Archivos Desarrollados y Propiedad

En estricto apego al contrato de responsabilidades por rol:
- `database/seeders/RolesYPermisosSeeder.php`: Seeder de Spatie Permission que inicializa los 4 permisos del sistema (`estudiantes.ver`, `estudiantes.crear`, `estudiantes.editar`, `estudiantes.eliminar`) y los roles `administrador`, `profesor`, `estudiante` y `coordinador` (Actividad 3) utilizando los Enums tipados `App\Enums\Rol` y `App\Enums\Permiso`.
- `database/seeders/DatosPruebaSeeder.php`: Seeder de datos iniciales que crea las Sedes (Sede Central y Sede Regional), las Carreras vinculadas (ITI e ISW), los 5 usuarios de prueba requeridos más el coordinador, y los 2 estudiantes base (Ana en ITI y Luis en ISW).
- `database/seeders/DatabaseSeeder.php`: Orquestador principal que ejecuta ordenadamente `RolesYPermisosSeeder` y `DatosPruebaSeeder`.
- `app/Providers/AppServiceProvider.php`: Registro de Gates en el método `boot()`:
  - `access-admin-dashboard`: Exclusivo para usuarios con rol `administrador`.
  - `access-professor-dashboard` (Actividad 1): Exclusivo para usuarios con rol `profesor` (deniega acceso a administradores y estudiantes).
- `routes/api/dashboard.php`: Rutas protegidas bajo el middleware `auth:sanctum`:
  - `GET /api/admin/dashboard`: Protegido con `Gate::authorize('access-admin-dashboard')`.
  - `GET /api/profesor/dashboard`: Protegido con `Gate::authorize('access-professor-dashboard')`.
- `docs/evidencias/p3/`: Colección Postman v2.1, colección Bruno, registro de pruebas Tinker y matriz de respuestas HTTP.

---

## 2. Comandos Ejecutados para Migración y Seeders

```bash
# 1. Reconstruir base de datos y sembrar datos de prueba
php artisan migrate:fresh --seed

# 2. Reiniciar la caché de permisos de Spatie
php artisan permission:cache-reset

# 3. Verificar rutas API registradas
php artisan route:list --path=api
```

### Salida de `php artisan route:list --path=api`:
```text
  GET|HEAD   api/admin/dashboard .......................................................... routes/api/dashboard.php:8
  POST       api/login .............................................................. login › Api\AuthController@login
  POST       api/logout .................................................................... Api\AuthController@logout
  GET|HEAD   api/me ............................................................................ Api\AuthController@me
  GET|HEAD   api/profesor/dashboard ...................................................... routes/api/dashboard.php:17
  POST       api/register ................................................................ Api\AuthController@register
  GET|HEAD   api/user ............................................................................... routes/api.php:6
                                                                                                    Showing [7] routes
```

---

## 3. Verificación de Roles y Permisos desde Tinker (Sección 29)

Se verificó el correcto mapeo de permisos y roles utilizando los métodos provistos por el trait `HasRoles` de Spatie:

```text
====================================================================
 EVIDENCIA P3: VERIFICACION DESDE TINKER - ROLES Y PERMISOS (RBAC)
====================================================================

>>> $user = App\Models\User::where('email', 'admin@academica.test')->first();
=> App\Models\User {#id: 1, name: "Administrador General", email: "admin@academica.test"}

>>> $user->getRoleNames();
=> [
    "administrador"
]

>>> $user->getAllPermissions()->pluck('name');
=> [
    "estudiantes.ver",
    "estudiantes.crear",
    "estudiantes.editar",
    "estudiantes.eliminar"
]

>>> $user->can('estudiantes.crear');
=> true

>>> $user->can('estudiantes.editar');
=> true

>>> Gate::forUser($user)->allows('access-admin-dashboard');
=> true

>>> Gate::forUser($user)->allows('access-professor-dashboard');
=> false

--------------------------------------------------------------------

>>> $user = App\Models\User::where('email', 'profesor.iti@academica.test')->first();
=> App\Models\User {#id: 2, name: "Profesor de TI", email: "profesor.iti@academica.test"}

>>> $user->getRoleNames();
=> [
    "profesor"
]

>>> $user->getAllPermissions()->pluck('name');
=> [
    "estudiantes.ver",
    "estudiantes.editar"
]

>>> $user->can('estudiantes.crear');
=> false

>>> $user->can('estudiantes.editar');
=> true

>>> Gate::forUser($user)->allows('access-admin-dashboard');
=> false

>>> Gate::forUser($user)->allows('access-professor-dashboard');
=> true

--------------------------------------------------------------------

>>> $user = App\Models\User::where('email', 'profesor.isw@academica.test')->first();
=> App\Models\User {#id: 3, name: "Profesor de Software", email: "profesor.isw@academica.test"}

>>> $user->getRoleNames();
=> [
    "profesor"
]

>>> $user->getAllPermissions()->pluck('name');
=> [
    "estudiantes.ver",
    "estudiantes.editar"
]

>>> $user->can('estudiantes.crear');
=> false

>>> $user->can('estudiantes.editar');
=> true

>>> Gate::forUser($user)->allows('access-admin-dashboard');
=> false

>>> Gate::forUser($user)->allows('access-professor-dashboard');
=> true

--------------------------------------------------------------------

>>> $user = App\Models\User::where('email', 'estudiante1@academica.test')->first();
=> App\Models\User {#id: 4, name: "Ana Estudiante", email: "estudiante1@academica.test"}

>>> $user->getRoleNames();
=> [
    "estudiante"
]

>>> $user->getAllPermissions()->pluck('name');
=> [
    "estudiantes.ver"
]

>>> $user->can('estudiantes.crear');
=> false

>>> $user->can('estudiantes.editar');
=> false

>>> Gate::forUser($user)->allows('access-admin-dashboard');
=> false

>>> Gate::forUser($user)->allows('access-professor-dashboard');
=> false

--------------------------------------------------------------------

>>> $user = App\Models\User::where('email', 'coordinador@academica.test')->first();
=> App\Models\User {#id: 6, name: "Coordinador Académico", email: "coordinador@academica.test"}

>>> $user->getRoleNames();
=> [
    "coordinador"
]

>>> $user->getAllPermissions()->pluck('name');
=> [
    "estudiantes.ver",
    "estudiantes.editar"
]

>>> $user->can('estudiantes.crear');
=> false

>>> $user->can('estudiantes.editar');
=> true

>>> Gate::forUser($user)->allows('access-admin-dashboard');
=> false

>>> Gate::forUser($user)->allows('access-professor-dashboard');
=> false
```

---

## 4. Matriz de Autorización de Gates y Respuestas HTTP (Secciones 25 y 31)

Evaluación de los endpoints `/api/admin/dashboard` y `/api/profesor/dashboard` frente a cada rol y estado de autenticación:

| Usuario / Credencial | Rol | `/api/admin/dashboard` | `/api/profesor/dashboard` | Cumplimiento |
|---|---|---|---|---|
| **Sin Token** | No autenticado | `401 Unauthorized` | `401 Unauthorized` | Autenticación Sanctum exigida |
| **admin@academica.test** | `administrador` | `200 OK` | `403 Forbidden` | Solo admin en dashboard admin |
| **profesor.iti@academica.test** | `profesor` | `403 Forbidden` | `200 OK` | Solo profesor en dashboard docente (Actividad 1) |
| **profesor.isw@academica.test** | `profesor` | `403 Forbidden` | `200 OK` | Solo profesor en dashboard docente (Actividad 1) |
| **estudiante1@academica.test** | `estudiante` | `403 Forbidden` | `403 Forbidden` | Acceso denegado a ambos |
| **estudiante2@academica.test** | `estudiante` | `403 Forbidden` | `403 Forbidden` | Acceso denegado a ambos |
| **coordinador@academica.test** | `coordinador` | `403 Forbidden` | `403 Forbidden` | Acceso denegado a dashboards exclusivos |

---

## 5. Respuestas JSON de Ejemplo

### Caso 1: Acceso Autorizado al Dashboard Administrativo (Admin)
- **Solicitud:** `GET /api/admin/dashboard`
- **Encabezados:** `Authorization: Bearer <TOKEN_ADMIN>`
- **Estado HTTP:** `200 OK`
```json
{
  "message": "Bienvenido al dashboard administrativo",
  "user": "Administrador General"
}
```

### Caso 2: Intento Denegado de Profesor a Dashboard Administrativo
- **Solicitud:** `GET /api/admin/dashboard`
- **Encabezados:** `Authorization: Bearer <TOKEN_PROFESOR>`
- **Estado HTTP:** `403 Forbidden`
```json
{
  "message": "This action is unauthorized."
}
```

### Caso 3: Acceso Autorizado al Dashboard Docente (Profesor ITI / ISW)
- **Solicitud:** `GET /api/profesor/dashboard`
- **Encabezados:** `Authorization: Bearer <TOKEN_PROFESOR>`
- **Estado HTTP:** `200 OK`
```json
{
  "message": "Bienvenido al dashboard de profesores",
  "user": "Profesor de TI"
}
```

### Caso 4: Intento Denegado de Administrador al Dashboard Docente (Actividad 1)
- **Solicitud:** `GET /api/profesor/dashboard`
- **Encabezados:** `Authorization: Bearer <TOKEN_ADMIN>`
- **Estado HTTP:** `403 Forbidden`
```json
{
  "message": "This action is unauthorized."
}
```

### Caso 5: Solicitud sin Credenciales (Sin Token)
- **Solicitud:** `GET /api/admin/dashboard`
- **Estado HTTP:** `401 Unauthorized`
```json
{
  "message": "Unauthenticated."
}
```

---

## 6. Estructura de Colecciones para Pruebas

En este directorio se incluyen:
1. `dashboards_postman_collection.json`: Colección compatible con Postman / Insomnia conteniendo todas las solicitudes parametrizadas con variables de entorno (`baseUrl`, `adminToken`, `profesorToken`, `estudianteToken`).
2. `bruno/`: Archivos `.bru` para ejecución rápida con Bruno CLI o GUI.
3. `roles_permisos_tinker.txt`: Registro detallado de la ejecución interactiva en Tinker.
4. `matriz_gates_dashboards.json`: Archivo JSON estructurado con la matriz de resultados HTTP y evaluación de Gates.
