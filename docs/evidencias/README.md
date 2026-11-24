# Consolidado de Evidencias de Integración (Secciones 22–28 y 36–38)

**Proyecto:** API REST Académica de Estudiantes (Laravel 13)  
**Integrador:** Persona 5 (P5) — Rama `p5-tests-entrega`  
**Base de integración:** `origin/main` (`9523df8`)  

---

## 1. Índice de Evidencias por Responsable

| Responsable | Rama | Alcance | Estado en `main` | Evidencias |
|---|---|---|---|---|
| **P1** | `p1-base` | Laravel 13, SQLite, Sanctum, Spatie, migraciones, modelos, Enums, `bootstrap/app.php`, `routes/api.php` | Integrado y verificado | Esquema migrado (`9` migraciones OK), alias `permission` registrado |
| **P2** | `p2-auth` | `AuthController` (`register`, `login`, `me`, `logout`) y `routes/api/auth.php` | Integrado y verificado | [docs/evidencias/p2/README.md](p2/README.md) |
| **P3** | `p3-rbac-gates` | Seeders RBAC/datos, Gates (`access-admin-dashboard`, `access-professor-dashboard`), `routes/api/dashboard.php` | Integrado y verificado | [docs/evidencias/p3/README.md](p3/README.md) |
| **P4** | `p4-policies-abac` | `EstudiantePolicy` (ABAC + inactivos), `EstudianteController`, `routes/api/estudiantes.php` con `permission:estudiantes.crear` | **Incompleto en `main`** (ver Sección 4) | Sin carpeta `docs/evidencias/p4/` |
| **P5** | `p5-tests-entrega` | `AutorizacionEstudiantesTest`, colección consolidada (`coleccion/`), `docs/reflexion.md`, matriz `401 vs 403`, auditoría | Entregado en esta rama | [docs/evidencias/p5/README.md](p5/README.md) |

---

## 2. Matriz Comparativa `401 Unauthorized` vs `403 Forbidden` (Secciones 22–28)

La separación entre **autenticación (`401`)** y **autorización (`403`)** garantiza que el servidor distinga entre un cliente sin identidad verificada y un usuario autenticado sin privilegios suficientes:

| Escenario / Endpoint | Método | Credencial / Rol | Código Esperado | Estado Actual en `main` | Causa / Mecanismo |
|---|---|---|---|---|---|
| `/api/login` (credenciales inválidas) | `POST` | Sin token | `401 Unauthorized` | `401` (OK - P2) | `AuthController::login` rechaza contraseña inválida |
| `/api/me` | `GET` | Sin token / token revocado | `401 Unauthorized` | `401` (OK - P2) | Middleware `auth:sanctum` |
| `/api/me` | `GET` | Token Bearer válido | `200 OK` | `200` (OK - P2) | `AuthController::me` |
| `/api/logout` | `POST` | Sin token | `401 Unauthorized` | `401` (OK - P2) | Middleware `auth:sanctum` |
| `/api/admin/dashboard` | `GET` | Sin token | `401 Unauthorized` | `401` (OK - P3) | Middleware `auth:sanctum` |
| `/api/admin/dashboard` | `GET` | `administrador` | `200 OK` | `200` (OK - P3) | Gate `access-admin-dashboard` |
| `/api/admin/dashboard` | `GET` | `profesor` / `estudiante` / `coordinador` | `403 Forbidden` | `403` (OK - P3) | Gate `access-admin-dashboard` deniega rol |
| `/api/profesor/dashboard` | `GET` | Sin token | `401 Unauthorized` | `401` (OK - P3) | Middleware `auth:sanctum` |
| `/api/profesor/dashboard` | `GET` | `profesor` | `200 OK` | `200` (OK - P3) | Gate `access-professor-dashboard` (Actividad 1) |
| `/api/profesor/dashboard` | `GET` | `administrador` / `estudiante` / `coordinador` | `403 Forbidden` | `403` (OK - P3) | Gate `access-professor-dashboard` deniega rol |
| `/api/estudiantes` | `GET` | Sin token | `401 Unauthorized` | `404` (**Bloqueado por P4**) | Falta registrar ruta en `routes/api/estudiantes.php` |
| `/api/estudiantes` | `POST` | Sin token | `401 Unauthorized` | `404` (**Bloqueado por P4**) | Falta registrar ruta en `routes/api/estudiantes.php` |
| `/api/estudiantes` | `POST` | `administrador` | `201 Created` | `404` (**Bloqueado por P4**) | Middleware `permission:estudiantes.crear` + `EstudiantePolicy::create` |
| `/api/estudiantes` | `POST` | `profesor` / `estudiante` / `coordinador` | `403 Forbidden` | `404` (**Bloqueado por P4**) | Middleware RBAC `permission:estudiantes.crear` |
| `/api/estudiantes/{ana}` | `GET` | `administrador` / `estudiante1` (Ana) / `profesor.iti` | `200 OK` | `404` (**Bloqueado por P4**) | `EstudiantePolicy::view` |
| `/api/estudiantes/{luis}` | `GET` | `estudiante1` (Ana) / `profesor.iti` | `403 Forbidden` | `404` (**Bloqueado por P4**) | Propiedad (`user_id`) y carrera (`carrera_id`) en Policy |
| `/api/estudiantes/{inactivo}` | `GET` | Otro `estudiante` / `profesor` / `coordinador` | `403 Forbidden` | `404` (**Bloqueado por P4**) | Regla de registros inactivos (`activo = false`, Actividad 2) |
| `/api/estudiantes/{ana}` | `PUT` | `profesor.iti` / `coordinador` (misma carrera y sede) | `200 OK` | `404` (**Bloqueado por P4**) | ABAC en `EstudiantePolicy::update` |
| `/api/estudiantes/{ana}` | `PUT` | `profesor` misma carrera, **sede diferente** | `403 Forbidden` | `404` (**Bloqueado por P4**) | ABAC por `sede_id` (Actividad 4) |
| `/api/estudiantes/{luis}` | `PUT` | `profesor.iti` (**otra carrera**, misma o distinta sede) | `403 Forbidden` | `404` (**Bloqueado por P4**) | ABAC por `carrera_id` y `sede_id` |
| `/api/estudiantes/{ana}` | `PUT` | `estudiante1` (Ana) | `403 Forbidden` | `404` (**Bloqueado por P4**) | Sin permiso `estudiantes.editar` |
| `/api/estudiantes/{ana}` | `DELETE` | `administrador` | `200 OK` | `404` (**Bloqueado por P4**) | `EstudiantePolicy::delete` (`estudiantes.eliminar`) |
| `/api/estudiantes/{ana}` | `DELETE` | `profesor` / `coordinador` / `estudiante` | `403 Forbidden` | `404` (**Bloqueado por P4**) | Sin permiso `estudiantes.eliminar` |

---

## 3. Verificación de Middleware de Permisos (Sección 20)

- **Alias `permission` en `bootstrap/app.php` (P1):** **VERIFICADO.** Está registrado correctamente como `'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class`.
- **Middleware en `POST /api/estudiantes` (P4):** **FALLA DE VERIFICACIÓN.** Al ejecutar `php artisan route:list --path=api`, solo existen 7 rutas (`auth` y `dashboard`). El archivo `routes/api/estudiantes.php` contiene únicamente `<?php` y no declara el recurso `estudiantes` ni `->middleware('permission:estudiantes.crear')`.

---

## 4. Solicitud Formal de Cambios al Dueño P4 (Regla de Propiedad de Archivos)

De acuerdo con la regla global (*"Cada persona es dueña de sus archivos. Si necesitas cambiar un archivo ajeno, no lo edites: escribe el cambio en tu PR como solicitud al dueño"*), **P5 no modificó ningún archivo de P4**. Se reportan los siguientes bloqueantes detectados al integrar `main` (`9523df8`):

1. **Crear `app/Http/Controllers/Api/EstudianteController.php`:** actualmente no existe en el repositorio. Debe implementar `index`, `show`, `store`, `update` y `destroy` con `Gate::authorize(...)` y validación `Rule::unique()->ignore($estudiante->id)` (sin permitir modificar `user_id` en `update`).
2. **Completar `routes/api/estudiantes.php`:** actualmente está vacío (`<?php`). Debe registrar las rutas CRUD de `/estudiantes` bajo `auth:sanctum` y proteger `POST /estudiantes` con `->middleware('permission:estudiantes.crear')`.
3. **Ajustar `app/Policies/EstudiantePolicy.php` al contrato del taller:**
   - En `viewAny`: actualmente excluye a `estudiante`; el enunciado indica que cualquier usuario autenticado con permiso `estudiantes.ver` (`$user->can(Permiso::Ver->value)`) puede listar estudiantes.
   - En `view`: actualmente no permite que un `estudiante` consulte su propio registro (`$estudiante->user_id === $user->id`) ni bloquea registros inactivos (`$estudiante->activo === false`, Actividad 2) para `profesor`/`coordinador`.
   - En `create`: actualmente permite al rol `coordinador`; según el enunciado y RBAC, solo `administrador` posee el permiso `estudiantes.crear`.
   - En `update`: actualmente excluye al rol `profesor` y compara `$user->sede_id === $estudiante->sede_id`, pero la tabla `estudiantes` no tiene columna `sede_id` (reside en `$estudiante->carrera->sede_id`). Debe verificar permiso `estudiantes.editar` junto con `$user->carrera_id === $estudiante->carrera_id && $user->sede_id !== null && $user->sede_id === $estudiante->carrera?->sede_id` tanto para `profesor` como para `coordinador`.
4. **Evidencias de P4:** agregar la matriz y evidencias de Policies/ABAC en `docs/evidencias/p4/`.

Una vez que P4 aplique estas correcciones y haga merge a `main`, los 11 tests pendientes de `AutorizacionEstudiantesTest` pasarán automáticamente en verde.

---

## 5. Lista de Verificación Final (Sección 38)

- [x] El proyecto se ejecuta con Laravel 13 (`Laravel Framework 13.43.0` sobre PHP 8.4.23).
- [x] Las migraciones se ejecutan correctamente (`php artisan migrate:fresh --seed` exitoso).
- [x] Sanctum permite iniciar sesión y generar tokens (`POST /api/register`, `POST /api/login`, `GET /api/me`, `POST /api/logout`).
- [x] Las rutas protegidas rechazan solicitudes sin token (`401 Unauthorized` verificado en `auth` y `dashboard`; cubierto en tests para `estudiantes`).
- [x] El Gate restringe el dashboard administrativo (`access-admin-dashboard`) y el dashboard de profesores (`access-professor-dashboard`).
- [ ] Las Policies controlan las operaciones sobre estudiantes *(pendiente de corrección en `EstudiantePolicy`, `EstudianteController` y `routes/api/estudiantes.php` por P4)*.
- [x] Spatie administra roles y permisos (`administrador`, `profesor`, `estudiante`, `coordinador` y 4 permisos verificados).
- [ ] Las reglas ABAC verifican atributos del usuario y del recurso en ejecución HTTP *(tests listos en `AutorizacionEstudiantesTest`; bloqueado por P4)*.
- [ ] Un estudiante no puede consultar el registro de otro en ejecución HTTP *(test listo; bloqueado por P4)*.
- [ ] Un profesor no puede modificar estudiantes de otra carrera o sede en ejecución HTTP *(tests listos; bloqueado por P4)*.
- [ ] Todas las pruebas automatizadas pasan en verde *(6/17 pasan actualmente: P1, P2 y P3 en verde; 11/17 fallan con `404` esperando los endpoints de P4)*.
- [x] Colección consolidada disponible en `coleccion/` (Postman + Bruno con carpetas `Auth`, `Dashboards`, `Estudiantes`) sin tokens reales.
- [x] Reflexión de seguridad (Actividad 5) completa en [docs/reflexion.md](../reflexion.md).
- [x] Repositorio limpio sin `vendor/`, `.env`, `database/database.sqlite` ni tokens personales en Git.
