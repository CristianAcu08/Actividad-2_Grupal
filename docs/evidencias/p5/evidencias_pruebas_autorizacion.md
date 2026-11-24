# Evidencias de Pruebas de Autorización (HTTP Reales)

**Proyecto:** API REST Académica de Estudiantes (Laravel 13.43.0 / PHP 8.4.23)  
**Fecha de ejecución:** 2026-11-24 03:38:02  
**Resultado global:** **35/35 casos aprobados** (0 fallidos)  

> Todos los tokens personales fueron sanitizados como `<TOKEN_SANITIZADO>` y las trazas internas de depuración fueron omitidas para mantener la evidencia limpia y segura.

---

## 1. Matriz Resumen de los 35 Casos Ejecutados

| # | Categoría | Caso de Prueba | Usuario / Actor | Endpoint | Esperado | Obtenido | Estado |
|---:|---|---|---|---|---:|---:|---|
| 1 | Autenticación (Sanctum) | Login válido Administrador | `admin@academica.test` | `POST /api/login` | `200` | `200` | **APROBADO** |
| 2 | Autenticación (Sanctum) | Consulta /api/me con Bearer Token | `admin@academica.test` | `GET /api/me` | `200` | `200` | **APROBADO** |
| 3 | Autenticación (Sanctum) | Consulta /api/me sin token (401) | `Sin token` | `GET /api/me` | `401` | `401` | **APROBADO** |
| 4 | Autenticación (Sanctum) | Listado /api/estudiantes sin token (401) | `Sin token` | `GET /api/estudiantes` | `401` | `401` | **APROBADO** |
| 5 | Gates (Dashboards) | Dashboard admin - Administrador | `admin@academica.test` | `GET /api/admin/dashboard` | `200` | `200` | **APROBADO** |
| 6 | Gates (Dashboards) | Dashboard admin - Profesor ITI | `profesor.iti@academica.test` | `GET /api/admin/dashboard` | `403` | `403` | **APROBADO** |
| 7 | Gates (Dashboards) | Dashboard admin - Estudiante Ana | `estudiante1@academica.test` | `GET /api/admin/dashboard` | `403` | `403` | **APROBADO** |
| 8 | Gates (Dashboards) | Dashboard admin - Sin token | `Sin token` | `GET /api/admin/dashboard` | `401` | `401` | **APROBADO** |
| 9 | Gates (Actividad 1) | Dashboard profesor - Profesor ITI | `profesor.iti@academica.test` | `GET /api/profesor/dashboard` | `200` | `200` | **APROBADO** |
| 10 | Gates (Actividad 1) | Dashboard profesor - Administrador | `admin@academica.test` | `GET /api/profesor/dashboard` | `403` | `403` | **APROBADO** |
| 11 | Gates (Actividad 1) | Dashboard profesor - Estudiante Ana | `estudiante1@academica.test` | `GET /api/profesor/dashboard` | `403` | `403` | **APROBADO** |
| 12 | Gates (Actividad 1) | Dashboard profesor - Coordinador | `coordinador@academica.test` | `GET /api/profesor/dashboard` | `403` | `403` | **APROBADO** |
| 13 | RBAC (Middleware + Policy) | Crear estudiante - Profesor denegado (middleware) | `profesor.iti@academica.test` | `POST /api/estudiantes` | `403` | `403` | **APROBADO** |
| 14 | RBAC (Middleware + Policy) | Crear estudiante - Estudiante denegado (middleware) | `estudiante1@academica.test` | `POST /api/estudiantes` | `403` | `403` | **APROBADO** |
| 15 | RBAC (Middleware + Policy) | Crear estudiante - Coordinador denegado (middleware) | `coordinador@academica.test` | `POST /api/estudiantes` | `403` | `403` | **APROBADO** |
| 16 | RBAC (Middleware + Policy) | Crear estudiante - Administrador autorizado | `admin@academica.test` | `POST /api/estudiantes` | `201` | `201` | **APROBADO** |
| 17 | Policies (Consulta/Propiedad) | GET detalle Ana - Administrador | `admin@academica.test` | `GET /api/estudiantes/1` | `200` | `200` | **APROBADO** |
| 18 | Policies (Consulta/Propiedad) | GET detalle Ana - Propio registro Estudiante Ana | `estudiante1@academica.test` | `GET /api/estudiantes/1` | `200` | `200` | **APROBADO** |
| 19 | Policies (Consulta/Propiedad) | GET detalle Luis - Estudiante Ana (registro ajeno) | `estudiante1@academica.test` | `GET /api/estudiantes/2` | `403` | `403` | **APROBADO** |
| 20 | Policies (Consulta/Propiedad) | GET detalle Ana - Profesor ITI (misma carrera/sede) | `profesor.iti@academica.test` | `GET /api/estudiantes/1` | `200` | `200` | **APROBADO** |
| 21 | Policies (Consulta/Propiedad) | GET detalle Luis - Profesor ITI (otra carrera) | `profesor.iti@academica.test` | `GET /api/estudiantes/2` | `403` | `403` | **APROBADO** |
| 22 | Actividad 2 (Registro Inactivo) | GET registro inactivo - Administrador | `admin@academica.test` | `GET /api/estudiantes/3` | `200` | `200` | **APROBADO** |
| 23 | Actividad 2 (Registro Inactivo) | GET registro inactivo - Profesor ITI (misma carrera/sede) | `profesor.iti@academica.test` | `GET /api/estudiantes/3` | `403` | `403` | **APROBADO** |
| 24 | Actividad 2 (Registro Inactivo) | GET registro inactivo - Coordinador ITI (misma carrera/sede) | `coordinador@academica.test` | `GET /api/estudiantes/3` | `403` | `403` | **APROBADO** |
| 25 | Actividad 2 (Registro Inactivo) | GET registro inactivo - Estudiante Ana (misma carrera/sede) | `estudiante1@academica.test` | `GET /api/estudiantes/3` | `403` | `403` | **APROBADO** |
| 26 | ABAC (Actividad 4 - Escenario 1) | PUT Ana - Profesor ITI (misma carrera + misma sede) | `profesor.iti@academica.test` | `PUT /api/estudiantes/1` | `200` | `200` | **APROBADO** |
| 27 | ABAC (Actividad 4 - Escenario 2) | PUT Ana - Profesor ITI SR (misma carrera + sede diferente) | `profesor.iti.sr@academica.test` | `PUT /api/estudiantes/1` | `403` | `403` | **APROBADO** |
| 28 | ABAC (Actividad 4 - Escenario 3) | PUT Luis - Profesor ITI SC (carrera diferente + misma sede) | `profesor.iti@academica.test` | `PUT /api/estudiantes/2` | `403` | `403` | **APROBADO** |
| 29 | ABAC (Actividad 4 - Escenario 4) | PUT Mateo - Profesor ITI SC (carrera diferente + sede diferente) | `profesor.iti@academica.test` | `PUT /api/estudiantes/4` | `403` | `403` | **APROBADO** |
| 30 | ABAC (Actividad 3 - Coordinador) | PUT Ana - Coordinador ITI (misma carrera + misma sede) | `coordinador@academica.test` | `PUT /api/estudiantes/1` | `200` | `200` | **APROBADO** |
| 31 | ABAC (Actividad 3 - Coordinador) | PUT Luis - Coordinador ITI (otra carrera) | `coordinador@academica.test` | `PUT /api/estudiantes/2` | `403` | `403` | **APROBADO** |
| 32 | Policies (Modificación) | PUT Ana - Estudiante Ana (sin permiso editar) | `estudiante1@academica.test` | `PUT /api/estudiantes/1` | `403` | `403` | **APROBADO** |
| 33 | RBAC (Eliminación) | DELETE Carlos - Profesor ITI denegado | `profesor.iti@academica.test` | `DELETE /api/estudiantes/5` | `403` | `403` | **APROBADO** |
| 34 | RBAC (Eliminación) | DELETE Carlos - Coordinador denegado | `coordinador@academica.test` | `DELETE /api/estudiantes/5` | `403` | `403` | **APROBADO** |
| 35 | RBAC (Eliminación) | DELETE Carlos - Administrador autorizado | `admin@academica.test` | `DELETE /api/estudiantes/5` | `200` | `200` | **APROBADO** |

---

## 2. Respuestas JSON Capturadas por Escenario Clave

### Caso #1 — Login válido Administrador
- **Categoría:** Autenticación (Sanctum)
- **Actor:** `admin@academica.test`
- **Solicitud:** `POST /api/login`
- **Código HTTP:** `200` (esperado: `200`)

```json
{
    "message": "Inicio de sesión exitoso",
    "token": "<TOKEN_SANITIZADO>",
    "access_token": "<TOKEN_SANITIZADO>",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "name": "Administrador General",
        "email": "admin@academica.test",
        "email_verified_at": null,
        "created_at": "2026-11-24T03:37:57.000000Z",
        "updated_at": "2026-11-24T03:37:57.000000Z",
        "carrera_id": null,
        "sede_id": null
    }
}
```

### Caso #3 — Consulta /api/me sin token (401)
- **Categoría:** Autenticación (Sanctum)
- **Actor:** `Sin token`
- **Solicitud:** `GET /api/me`
- **Código HTTP:** `401` (esperado: `401`)

```json
{
    "message": "Unauthenticated."
}
```

### Caso #5 — Dashboard admin - Administrador
- **Categoría:** Gates (Dashboards)
- **Actor:** `admin@academica.test`
- **Solicitud:** `GET /api/admin/dashboard`
- **Código HTTP:** `200` (esperado: `200`)

```json
{
    "message": "Bienvenido al dashboard administrativo",
    "user": "Administrador General"
}
```

### Caso #6 — Dashboard admin - Profesor ITI
- **Categoría:** Gates (Dashboards)
- **Actor:** `profesor.iti@academica.test`
- **Solicitud:** `GET /api/admin/dashboard`
- **Código HTTP:** `403` (esperado: `403`)

```json
{
    "message": "This action is unauthorized.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException"
}
```

### Caso #9 — Dashboard profesor - Profesor ITI
- **Categoría:** Gates (Actividad 1)
- **Actor:** `profesor.iti@academica.test`
- **Solicitud:** `GET /api/profesor/dashboard`
- **Código HTTP:** `200` (esperado: `200`)

```json
{
    "message": "Bienvenido al dashboard de profesores",
    "user": "Profesor de TI"
}
```

### Caso #10 — Dashboard profesor - Administrador
- **Categoría:** Gates (Actividad 1)
- **Actor:** `admin@academica.test`
- **Solicitud:** `GET /api/profesor/dashboard`
- **Código HTTP:** `403` (esperado: `403`)

```json
{
    "message": "This action is unauthorized.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException"
}
```

### Caso #13 — Crear estudiante - Profesor denegado (middleware)
- **Categoría:** RBAC (Middleware + Policy)
- **Actor:** `profesor.iti@academica.test`
- **Solicitud:** `POST /api/estudiantes`
- **Código HTTP:** `403` (esperado: `403`)

```json
{
    "message": "User does not have the right permissions.",
    "exception": "Spatie\\Permission\\Exceptions\\UnauthorizedException"
}
```

### Caso #16 — Crear estudiante - Administrador autorizado
- **Categoría:** RBAC (Middleware + Policy)
- **Actor:** `admin@academica.test`
- **Solicitud:** `POST /api/estudiantes`
- **Código HTTP:** `201` (esperado: `201`)

```json
{
    "user_id": 7,
    "carrera_id": 1,
    "cedula": "3-3333-3333",
    "nombre": "Carlos",
    "apellido": "Ejemplo",
    "correo_institucional": "carlos@estudiantes.test",
    "activo": true,
    "updated_at": "2026-11-24T03:38:01.000000Z",
    "created_at": "2026-11-24T03:38:01.000000Z",
    "id": 5
}
```

### Caso #18 — GET detalle Ana - Propio registro Estudiante Ana
- **Categoría:** Policies (Consulta/Propiedad)
- **Actor:** `estudiante1@academica.test`
- **Solicitud:** `GET /api/estudiantes/1`
- **Código HTTP:** `200` (esperado: `200`)

```json
{
    "id": 1,
    "user_id": 4,
    "carrera_id": 1,
    "cedula": "1-1111-1111",
    "nombre": "Ana",
    "apellido": "Estudiante",
    "correo_institucional": "ana@estudiantes.test",
    "activo": true,
    "created_at": "2026-11-24T03:37:58.000000Z",
    "updated_at": "2026-11-24T03:37:58.000000Z",
    "carrera": {
        "id": 1,
        "nombre": "Ingeniería en Tecnologías de Información",
        "codigo": "ITI",
        "sede_id": 1,
        "created_at": "2026-11-24T03:37:57.000000Z",
        "updated_at": "2026-11-24T03:37:57.000000Z",
        "sede": {
            "id": 1,
            "nombre": "Sede Central",
            "codigo": "SC",
            "created_at": "2026-11-24T03:37:57.000000Z",
            "updated_at": "2026-11-24T03:37:57.000000Z"
        }
    },
    "usuario": {
        "id": 4,
        "name": "Ana Estudiante",
        "email": "estudiante1@academica.test",
        "email_verified_at": null,
        "created_at": "2026-11-24T03:37:58.000000Z",
        "updated_at": "2026-11-24T03:37:58.000000Z",
        "carrera_id": 1,
        "sede_id": 1
    }
}
```

### Caso #19 — GET detalle Luis - Estudiante Ana (registro ajeno)
- **Categoría:** Policies (Consulta/Propiedad)
- **Actor:** `estudiante1@academica.test`
- **Solicitud:** `GET /api/estudiantes/2`
- **Código HTTP:** `403` (esperado: `403`)

```json
{
    "message": "Solo puede consultar su propio registro estudiantil.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException"
}
```

### Caso #23 — GET registro inactivo - Profesor ITI (misma carrera/sede)
- **Categoría:** Actividad 2 (Registro Inactivo)
- **Actor:** `profesor.iti@academica.test`
- **Solicitud:** `GET /api/estudiantes/3`
- **Código HTTP:** `403` (esperado: `403`)

```json
{
    "message": "No tiene permiso para ver este estudiante.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException"
}
```

### Caso #26 — PUT Ana - Profesor ITI (misma carrera + misma sede)
- **Categoría:** ABAC (Actividad 4 - Escenario 1)
- **Actor:** `profesor.iti@academica.test`
- **Solicitud:** `PUT /api/estudiantes/1`
- **Código HTTP:** `200` (esperado: `200`)

```json
{
    "id": 1,
    "user_id": 4,
    "carrera_id": 1,
    "cedula": "1-1111-1111",
    "nombre": "Ana María",
    "apellido": "Estudiante",
    "correo_institucional": "ana@estudiantes.test",
    "activo": true,
    "created_at": "2026-11-24T03:37:58.000000Z",
    "updated_at": "2026-11-24T03:38:01.000000Z",
    "carrera": {
        "id": 1,
        "nombre": "Ingeniería en Tecnologías de Información",
        "codigo": "ITI",
        "sede_id": 1,
        "created_at": "2026-11-24T03:37:57.000000Z",
        "updated_at": "2026-11-24T03:37:57.000000Z"
    }
}
```

### Caso #27 — PUT Ana - Profesor ITI SR (misma carrera + sede diferente)
- **Categoría:** ABAC (Actividad 4 - Escenario 2)
- **Actor:** `profesor.iti.sr@academica.test`
- **Solicitud:** `PUT /api/estudiantes/1`
- **Código HTTP:** `403` (esperado: `403`)

```json
{
    "message": "No tiene permiso para actualizar este estudiante.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException"
}
```

### Caso #28 — PUT Luis - Profesor ITI SC (carrera diferente + misma sede)
- **Categoría:** ABAC (Actividad 4 - Escenario 3)
- **Actor:** `profesor.iti@academica.test`
- **Solicitud:** `PUT /api/estudiantes/2`
- **Código HTTP:** `403` (esperado: `403`)

```json
{
    "message": "No tiene permiso para actualizar este estudiante.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException"
}
```

### Caso #29 — PUT Mateo - Profesor ITI SC (carrera diferente + sede diferente)
- **Categoría:** ABAC (Actividad 4 - Escenario 4)
- **Actor:** `profesor.iti@academica.test`
- **Solicitud:** `PUT /api/estudiantes/4`
- **Código HTTP:** `403` (esperado: `403`)

```json
{
    "message": "No tiene permiso para actualizar este estudiante.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException"
}
```

### Caso #30 — PUT Ana - Coordinador ITI (misma carrera + misma sede)
- **Categoría:** ABAC (Actividad 3 - Coordinador)
- **Actor:** `coordinador@academica.test`
- **Solicitud:** `PUT /api/estudiantes/1`
- **Código HTTP:** `200` (esperado: `200`)

```json
{
    "id": 1,
    "user_id": 4,
    "carrera_id": 1,
    "cedula": "1-1111-1111",
    "nombre": "Ana Actualizada Coord",
    "apellido": "Estudiante",
    "correo_institucional": "ana@estudiantes.test",
    "activo": true,
    "created_at": "2026-11-24T03:37:58.000000Z",
    "updated_at": "2026-11-24T03:38:01.000000Z",
    "carrera": {
        "id": 1,
        "nombre": "Ingeniería en Tecnologías de Información",
        "codigo": "ITI",
        "sede_id": 1,
        "created_at": "2026-11-24T03:37:57.000000Z",
        "updated_at": "2026-11-24T03:37:57.000000Z"
    }
}
```

### Caso #32 — PUT Ana - Estudiante Ana (sin permiso editar)
- **Categoría:** Policies (Modificación)
- **Actor:** `estudiante1@academica.test`
- **Solicitud:** `PUT /api/estudiantes/1`
- **Código HTTP:** `403` (esperado: `403`)

```json
{
    "message": "No tiene permiso para actualizar estudiantes.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException"
}
```

### Caso #33 — DELETE Carlos - Profesor ITI denegado
- **Categoría:** RBAC (Eliminación)
- **Actor:** `profesor.iti@academica.test`
- **Solicitud:** `DELETE /api/estudiantes/5`
- **Código HTTP:** `403` (esperado: `403`)

```json
{
    "message": "No tiene permiso para eliminar estudiantes.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException"
}
```

### Caso #35 — DELETE Carlos - Administrador autorizado
- **Categoría:** RBAC (Eliminación)
- **Actor:** `admin@academica.test`
- **Solicitud:** `DELETE /api/estudiantes/5`
- **Código HTTP:** `200` (esperado: `200`)

```json
{
    "message": "Estudiante eliminado correctamente"
}
```

---

El registro completo de los 35 casos en formato JSON estructurado se encuentra en [resultados_http_autorizacion.json](resultados_http_autorizacion.json).
