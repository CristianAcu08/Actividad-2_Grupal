# Evidencias de Entrega - P5: Pruebas, Colección Consolidada y Entrega Final

**Responsable:** Persona 5 (P5)  
**Rama de trabajo:** `p5-tests-entrega` (sobre `origin/main` en `9523df8`, preservando todo el historial de commits del equipo)  
**Módulos asignados:** Secciones 20 (verificación), 30, 35–38 y consolidación de evidencias 22–28.

---

## 1. Archivos Entregados por P5

- `tests/Feature/AutorizacionEstudiantesTest.php`: Suite completa con `RefreshDatabase` y uso de `App\Enums\Rol` y `App\Enums\Permiso` (15 casos de prueba).
- `coleccion/api_academica_postman_collection.json`: Colección Postman v2.1 consolidada con carpetas `Auth`, `Dashboards` y `Estudiantes`.
- `coleccion/bruno/`: Colección Bruno consolidada con `bruno.json`, `environments/Local.bru` y subcarpetas `Auth/`, `Dashboards/`, `Estudiantes/`.
- `coleccion/README.md`: Guía de uso de las colecciones.
- `docs/reflexion.md`: Respuestas técnicas a las 8 preguntas de la Actividad 5 (Sección 35).
- `docs/evidencias/README.md`: Consolidado de evidencias, matriz `401 vs 403` y checklist completo de la Sección 38.
- `docs/evidencias/p5/README.md`, `docs/evidencias/p5/salida_comandos.txt`, `docs/evidencias/p5/evidencias_pruebas_autorizacion.md` y `docs/evidencias/p5/resultados_http_autorizacion.json`: Registro de comandos ejecutados, salidas literales en verde y batería de **35 pruebas HTTP reales de autorización** (`35/35 aprobadas`).
- Commit de cierre de integración para completar el CRUD/ABAC de estudiantes (`EstudianteController`, `routes/api/estudiantes.php` y ajuste de `EstudiantePolicy`) sin eliminar ni reescribir ningún commit previo del equipo.

---

## 2. Comandos de Verificación Ejecutados

```bash
php artisan --version
php artisan migrate:fresh --seed
php artisan permission:cache-reset
php artisan route:list --path=api
php artisan test --filter=AutorizacionEstudiantesTest
php artisan test
```

### Resumen de Resultados Obtenidos

| Comando | Resultado | Observación |
|---|---|---|
| `php artisan --version` | `Laravel Framework 13.43.0` | Ejecutado sobre PHP 8.4.23 |
| `php artisan migrate:fresh --seed` | `OK` | 9 migraciones + `RolesYPermisosSeeder` + `DatosPruebaSeeder` |
| `php artisan permission:cache-reset` | `OK` | `Permission cache flushed.` |
| `php artisan route:list --path=api` | `13 rutas` | Todas las rutas de `auth`, `dashboard` y CRUD `estudiantes` registradas |
| `php artisan test --filter=AutorizacionEstudiantesTest` | **`15 passed (40 assertions)`** | 100% en verde (`1.27s`) |
| `php artisan test` | **`17 passed (42 assertions)`** | Suite completa en verde (`1.37s`) |

La salida literal completa de todos los comandos se conserva en [salida_comandos.txt](salida_comandos.txt).

---

## 3. Cobertura de `AutorizacionEstudiantesTest` (`15/15 PASS`)

1. **Pruebas base del enunciado (Sección 30):**
   - `test_student_can_view_own_record` (**PASS**)
   - `test_student_cannot_view_another_student_record` (**PASS**)
   - `test_professor_cannot_update_student_from_another_career` (**PASS**)
2. **Gates de Dashboards (Secciones 13–14 y Actividad 1):**
   - `test_admin_dashboard_gate_allows_only_administrators` (**PASS**)
   - `test_professor_dashboard_gate_allows_only_professors` (**PASS**)
3. **Autenticación `401` y seguridad de registro (Sección 28):**
   - `test_unauthenticated_requests_to_auth_and_dashboards_return_401` (**PASS**)
   - `test_unauthenticated_requests_to_estudiantes_return_401` (**PASS**)
   - `test_public_register_ignores_roles_and_permissions_payload` (**PASS**)
4. **Verificación de middleware RBAC `permission:estudiantes.crear` (Sección 20):**
   - `test_store_estudiante_route_has_permission_middleware` (**PASS**)
   - `test_only_administrator_can_create_and_delete_student` (**PASS**)
5. **Rol `coordinador` (Actividad 3):**
   - `test_coordinator_has_rbac_permissions_and_respects_abac_rules` (**PASS**)
6. **ABAC por Carrera y Sede (Actividad 4 — los 4 escenarios):**
   - `test_professor_can_update_student_with_same_career_and_same_sede` (**PASS**)
   - `test_professor_cannot_update_student_with_same_career_and_different_sede` (**PASS**)
   - `test_professor_cannot_update_student_from_another_career` (**PASS**)
   - `test_professor_cannot_update_student_with_different_career_and_different_sede` (**PASS**)
7. **Registro inactivo (Actividad 2):**
   - `test_inactive_student_record_cannot_be_viewed_by_other_student_professor_or_coordinator` (**PASS**)

---

## 4. Higiene de Seguridad del Commit

Verificado con `git status` antes de cada commit:
- Sin carpeta `vendor/`
- Sin archivo `.env`
- Sin archivo `database/database.sqlite`
- Sin tokens reales ni credenciales de producción en colecciones o documentación
