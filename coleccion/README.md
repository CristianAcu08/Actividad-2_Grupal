# Colección Consolidada de Pruebas (Postman y Bruno)

Este directorio consolida todas las solicitudes HTTP de la API Académica organizadas en tres carpetas lógicas:

- **`Auth/`**: Registro público anti-escalada, inicio de sesión por rol (`administrador`, `profesor`, `estudiante`, `coordinador`), login inválido (`401`), consulta `/api/me` (con token `200` y sin token `401`) y cierre de sesión (`/api/logout`).
- **`Dashboards/`**: Verificación de los Gates `access-admin-dashboard` y `access-professor-dashboard` con respuestas `200`, `403` y `401`.
- **`Estudiantes/`**: Operaciones CRUD sobre `/api/estudiantes`, verificación de middleware RBAC (`permission:estudiantes.crear`), políticas por propiedad y reglas ABAC de carrera/sede.

## Estructura

```text
coleccion/
├── README.md
├── api_academica_postman_collection.json   # Importable en Postman / Insomnia
└── bruno/
    ├── bruno.json
    ├── environments/
    │   └── Local.bru
    ├── Auth/
    ├── Dashboards/
    └── Estudiantes/
```

## Cómo utilizarla

1. Levantar la API localmente:
   ```bash
   php artisan migrate:fresh --seed
   php artisan serve
   ```
2. **En Postman / Insomnia:** importar `coleccion/api_academica_postman_collection.json` y ejecutar primero las solicitudes de login en la carpeta `Auth` para poblar automáticamente las variables `admin_token`, `profesor_iti_token`, `estudiante1_token` y `coordinador_token`.
3. **En Bruno:** abrir la carpeta `coleccion/bruno`, seleccionar el entorno `Local` y ejecutar las carpetas en orden (`Auth` → `Dashboards` → `Estudiantes`).

> **Nota de seguridad:** Ninguna variable de entorno o colección almacena tokens personales reales; los tokens se obtienen dinámicamente en cada sesión local.
