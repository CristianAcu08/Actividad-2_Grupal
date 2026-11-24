# Consolidado de Evidencias de Integración (Secciones 22–28 y 36–38)

**Proyecto:** API REST Académica de Estudiantes (Laravel 13)  
**Integrador:** Persona 5 (P5) — Rama `p5-tests-entrega`  
**Base de integración:** `origin/main` (`9523df8`)  

---

## 1. Índice de Evidencias por Responsable

| Responsable | Rama | Alcance | Estado Final | Evidencias |
|---|---|---|---|---|
| **P1** | `p1-base` | Laravel 13, SQLite, Sanctum, Spatie, migraciones, modelos, Enums, `bootstrap/app.php`, `routes/api.php` | Integrado y verificado | Esquema migrado (`9` migraciones OK), alias `permission` registrado |
| **P2** | `p2-auth` | `AuthController` (`register`, `login`, `me`, `logout`) y `routes/api/auth.php` | Integrado y verificado | [docs/evidencias/p2/README.md](p2/README.md) |
| **P3** | `p3-rbac-gates` | Seeders RBAC/datos, Gates (`access-admin-dashboard`, `access-professor-dashboard`), `routes/api/dashboard.php` | Integrado y verificado | [docs/evidencias/p3/README.md](p3/README.md) |
| **P4** | `p4-policies-abac` | `EstudiantePolicy` (ABAC + inactivos), `EstudianteController`, `routes/api/estudiantes.php` con `permission:estudiantes.crear` | Integrado y completado para entrega final | Rutas CRUD + Policies ABAC verificadas en verde |
| **P5** | `p5-tests-entrega` | `AutorizacionEstudiantesTest`, colección consolidada (`coleccion/`), `docs/reflexion.md`, matriz `401 vs 403`, verificación final | Integrado y verificado (`17/17` tests OK) | [docs/evidencias/p5/README.md](p5/README.md) |

---

## 2. Matriz Comparativa `401 Unauthorized` vs `403 Forbidden` (Secciones 22–28)

La separación entre **autenticación (`401`)** y **autorización (`403`)** garantiza que el servidor distinga entre un cliente sin identidad verificada y un usuario autenticado sin privilegios suficientes:

| Escenario / Endpoint | Método | Credencial / Rol | Código Esperado | Estado Obtenido | Causa / Mecanismo |
|---|---|---|---|---|---|
| `/api/login` (credenciales inválidas) | `POST` | Sin token | `401 Unauthorized` | `401` (OK) | `AuthController::login` rechaza contraseña inválida |
| `/api/me` | `GET` | Sin token / token revocado | `401 Unauthorized` | `401` (OK) | Middleware `auth:sanctum` |
| `/api/me` | `GET` | Token Bearer válido | `200 OK` | `200` (OK) | `AuthController::me` |
| `/api/logout` | `POST` | Sin token | `401 Unauthorized` | `401` (OK) | Middleware `auth:sanctum` |
| `/api/admin/dashboard` | `GET` | Sin token | `401 Unauthorized` | `401` (OK) | Middleware `auth:sanctum` |
| `/api/admin/dashboard` | `GET` | `administrador` | `200 OK` | `200` (OK) | Gate `access-admin-dashboard` |
| `/api/admin/dashboard` | `GET` | `profesor` / `estudiante` / `coordinador` | `403 Forbidden` | `403` (OK) | Gate `access-admin-dashboard` deniega rol |
| `/api/profesor/dashboard` | `GET` | Sin token | `401 Unauthorized` | `401` (OK) | Middleware `auth:sanctum` |
| `/api/profesor/dashboard` | `GET` | `profesor` | `200 OK` | `200` (OK) | Gate `access-professor-dashboard` (Actividad 1) |
| `/api/profesor/dashboard` | `GET` | `administrador` / `estudiante` / `coordinador` | `403 Forbidden` | `403` (OK) | Gate `access-professor-dashboard` deniega rol |
| `/api/estudiantes` | `GET` | Sin token | `401 Unauthorized` | `401` (OK) | Middleware `auth:sanctum` |
| `/api/estudiantes` | `POST` | Sin token | `401 Unauthorized` | `401` (OK) | Middleware `auth:sanctum` |
| `/api/estudiantes` | `POST` | `administrador` | `201 Created` | `201` (OK) | Middleware `permission:estudiantes.crear` + `EstudiantePolicy::create` |
| `/api/estudiantes` | `POST` | `profesor` / `estudiante` / `coordinador` | `403 Forbidden` | `403` (OK) | Middleware RBAC `permission:estudiantes.crear` |
| `/api/estudiantes/{ana}` | `GET` | `administrador` / `estudiante1` (Ana) / `profesor.iti` | `200 OK` | `200` (OK) | `EstudiantePolicy::view` |
| `/api/estudiantes/{luis}` | `GET` | `estudiante1` (Ana) / `profesor.iti` | `403 Forbidden` | `403` (OK) | Propiedad (`user_id`) y carrera (`carrera_id`) en Policy |
| `/api/estudiantes/{inactivo}` | `GET` | Otro `estudiante` / `profesor` / `coordinador` | `403 Forbidden` | `403` (OK) | Regla de registros inactivos (`activo = false`, Actividad 2) |
| `/api/estudiantes/{ana}` | `PUT` | `profesor.iti` / `coordinador` (misma carrera y sede) | `200 OK` | `200` (OK) | ABAC en `EstudiantePolicy::update` |
| `/api/estudiantes/{ana}` | `PUT` | `profesor` misma carrera, **sede diferente** | `403 Forbidden` | `403` (OK) | ABAC por `sede_id` (Actividad 4) |
| `/api/estudiantes/{luis}` | `PUT` | `profesor.iti` (**otra carrera**, misma o distinta sede) | `403 Forbidden` | `403` (OK) | ABAC por `carrera_id` y `sede_id` |
| `/api/estudiantes/{ana}` | `PUT` | `estudiante1` (Ana) | `403 Forbidden` | `403` (OK) | Sin permiso `estudiantes.editar` |
| `/api/estudiantes/{ana}` | `DELETE` | `administrador` | `200 OK` | `200` (OK) | `EstudiantePolicy::delete` (`estudiantes.eliminar`) |
| `/api/estudiantes/{ana}` | `DELETE` | `profesor` / `coordinador` / `estudiante` | `403 Forbidden` | `403` (OK) | Sin permiso `estudiantes.eliminar` |

---

## 3. Verificación de Middleware de Permisos (Sección 20)

- **Alias `permission` en `bootstrap/app.php`:** **VERIFICADO** (`Spatie\Permission\Middleware\PermissionMiddleware`).
- **Middleware en `POST /api/estudiantes`:** **VERIFICADO** mediante `php artisan route:list --path=api` (13 rutas activas) y la prueba automatizada `test_store_estudiante_route_has_permission_middleware` (`permission:estudiantes.crear` presente).

---

## 4. Nota de Integración Final

Durante la integración final se conservó íntegro el historial de commits de todos los compañeros (`P1`, `P2`, `P3` y `P4`) y se añadió un commit de cierre con la implementación faltante de `EstudianteController`, el registro de rutas CRUD en `routes/api/estudiantes.php` y el ajuste de `EstudiantePolicy` al contrato de Enums y ABAC (carrera + sede + registros inactivos), logrando que el 100% de la suite de pruebas (`17 passed, 42 assertions`) pase en verde.

---

## 5. Lista de Verificación Final (Sección 38)

- [x] El proyecto se ejecuta con Laravel 13 (`Laravel Framework 13.43.0` sobre PHP 8.4.23).
- [x] Las migraciones se ejecutan correctamente (`php artisan migrate:fresh --seed` exitoso).
- [x] Sanctum permite iniciar sesión y generar tokens (`POST /api/register`, `POST /api/login`, `GET /api/me`, `POST /api/logout`).
- [x] Las rutas protegidas rechazan solicitudes sin token (`401 Unauthorized` verificado en `auth`, `dashboard` y `estudiantes`).
- [x] El Gate restringe el dashboard administrativo (`access-admin-dashboard`) y el dashboard de profesores (`access-professor-dashboard`).
- [x] Las Policies controlan las operaciones sobre estudiantes (`EstudiantePolicy` + `EstudianteController`).
- [x] Spatie administra roles y permisos (`administrador`, `profesor`, `estudiante`, `coordinador` y 4 permisos verificados).
- [x] Las reglas ABAC verifican atributos del usuario y del recurso (`carrera_id` y `sede_id` en los 4 escenarios).
- [x] Un estudiante no puede consultar el registro de otro (`403 Forbidden`).
- [x] Un profesor no puede modificar estudiantes de otra carrera ni de otra sede (`403 Forbidden`).
- [x] Las pruebas automatizadas pasan correctamente (`17 passed, 42 assertions`).
- [x] Colección consolidada disponible en `coleccion/` (Postman + Bruno con carpetas `Auth`, `Dashboards`, `Estudiantes`) sin tokens reales.
- [x] Reflexión de seguridad (Actividad 5) completa en [docs/reflexion.md](../reflexion.md).
- [x] Repositorio limpio sin `vendor/`, `.env`, `database/database.sqlite` ni tokens personales en Git.
