# Evidencias de Entrega - P2: Autenticación con Sanctum

**Responsable:** Persona 2 (P2)  
**Rama de trabajo:** `p2-auth`  
**Módulos asignados:** Secciones 9, 21–24 y 28 del enunciado  

---

## 1. Archivos Desarrollados y Propiedad

En cumplimiento con la regla de propiedad de archivos:
- `app/Http/Controllers/Api/AuthController.php`: Controlador de autenticación con `register`, `login`, `me` y `logout`. Emite tokens con `createToken('api-academica')` y utiliza hashing de contraseñas.
- `routes/api/auth.php`: Definición de rutas públicas (`/register`, `/login`) y protegidas bajo el middleware `auth:sanctum` (`/me`, `/logout`).
- `docs/evidencias/p2/`: Colección Postman v2.1, colección Bruno y este documento de evidencias.

---

## 2. Verificación de Rutas Registradas

Comando ejecutado:
```bash
php artisan route:list --path=api
```

Salida obtenida:
```text
  POST       api/login ...................... login › Api\AuthController@login
  POST       api/logout ............................ Api\AuthController@logout
  GET|HEAD   api/me .................................... Api\AuthController@me
  POST       api/register ........................ Api\AuthController@register
  GET|HEAD   api/user ....................................... routes/api.php:6
                                                            Showing [5] routes
```

---

## 3. Matriz de Pruebas de Endpoints y Códigos HTTP

| Endpoint | Método HTTP | Autenticación Requerida | Estado Esperado | Estado Obtenido | Resultado |
|---|---|---|---|---|---|
| `/api/register` | `POST` | Pública | `201 Created` | `201 Created` | Aprobado |
| `/api/login` (válido) | `POST` | Pública | `200 OK` | `200 OK` | Aprobado |
| `/api/login` (inválido) | `POST` | Pública | `401 Unauthorized` | `401 Unauthorized` | Aprobado |
| `/api/me` (sin token) | `GET` | Protegida (`auth:sanctum`) | `401 Unauthorized` | `401 Unauthorized` | Aprobado |
| `/api/me` (con Bearer) | `GET` | Protegida (`auth:sanctum`) | `200 OK` | `200 OK` | Aprobado |
| `/api/logout` | `POST` | Protegida (`auth:sanctum`) | `200 OK` | `200 OK` | Aprobado |
| `/api/me` (token revocado) | `GET` | Protegida (`auth:sanctum`) | `401 Unauthorized` | `401 Unauthorized` | Aprobado |

---

## 4. Ejecución Detallada y Respuestas JSON

### 4.1. Registro de Usuario (`POST /api/register`) - 201 Created

Petición HTTP con intento de inyección de roles para validar anti-escalada de privilegios:
```bash
curl -i -X POST http://127.0.0.1:8000/api/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Persona Dos",
    "email": "p2@academica.test",
    "password": "Password123!",
    "rol": "administrador",
    "roles": ["administrador"],
    "permissions": ["estudiantes.crear", "estudiantes.eliminar"]
  }'
```

**Respuesta HTTP:**
```http
HTTP/1.1 201 Created
Content-Type: application/json
```
```json
{
    "message": "Usuario registrado exitosamente",
    "token": "1|Jun8dTcqtnAqIOjsDHZdbrjSbi3b4QR3Ab0hsm5Zd0fa35cf",
    "access_token": "1|Jun8dTcqtnAqIOjsDHZdbrjSbi3b4QR3Ab0hsm5Zd0fa35cf",
    "token_type": "Bearer",
    "user": {
        "name": "Persona Dos",
        "email": "p2@academica.test",
        "carrera_id": null,
        "sede_id": null,
        "updated_at": "2026-11-24T01:46:03.000000Z",
        "created_at": "2026-11-24T01:46:03.000000Z",
        "id": 1
    }
}
```

**Verificación de Seguridad en Base de Datos:**
```json
{
    "usuario_id": 1,
    "roles_asignados": [],
    "permisos_asignados": []
}
```
*Confirmación:* Los campos `rol`, `roles` y `permissions` enviados en el body fueron estrictamente descartados. El usuario no adquiere ningún rol ni permiso no autorizado.

---

### 4.2. Inicio de Sesión Exitoso (`POST /api/login`) - 200 OK

Petición HTTP:
```bash
curl -i -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "p2@academica.test",
    "password": "Password123!"
  }'
```

**Respuesta HTTP:**
```http
HTTP/1.1 200 OK
Content-Type: application/json
```
```json
{
    "message": "Inicio de sesión exitoso",
    "token": "2|t6X7nKM8QUoNC9hO7fSFbVDg3hIpLvxT6dufsoAq1124e78d",
    "access_token": "2|t6X7nKM8QUoNC9hO7fSFbVDg3hIpLvxT6dufsoAq1124e78d",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "name": "Persona Dos",
        "email": "p2@academica.test",
        "email_verified_at": null,
        "created_at": "2026-11-24T01:46:03.000000Z",
        "updated_at": "2026-11-24T01:46:03.000000Z",
        "carrera_id": null,
        "sede_id": null
    }
}
```

---

### 4.3. Inicio de Sesión Fallido (`POST /api/login`) - 401 Unauthorized

Petición HTTP con contraseña inválida:
```bash
curl -i -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "p2@academica.test",
    "password": "ClaveIncorrecta!"
  }'
```

**Respuesta HTTP:**
```http
HTTP/1.1 401 Unauthorized
Content-Type: application/json
```
```json
{
    "message": "Credenciales inválidas"
}
```

---

### 4.4. Consulta de Perfil sin Token (`GET /api/me`) - 401 Unauthorized

Petición HTTP sin cabecera `Authorization`:
```bash
curl -i -X GET http://127.0.0.1:8000/api/me \
  -H "Accept: application/json"
```

**Respuesta HTTP:**
```http
HTTP/1.1 401 Unauthorized
Content-Type: application/json
```
```json
{
    "message": "Unauthenticated."
}
```

---

### 4.5. Consulta de Perfil con Token Bearer (`GET /api/me`) - 200 OK

Petición HTTP autenticada con token emitido por Sanctum:
```bash
curl -i -X GET http://127.0.0.1:8000/api/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 2|t6X7nKM8QUoNC9hO7fSFbVDg3hIpLvxT6dufsoAq1124e78d"
```

**Respuesta HTTP:**
```http
HTTP/1.1 200 OK
Content-Type: application/json
```
```json
{
    "id": 1,
    "name": "Persona Dos",
    "email": "p2@academica.test",
    "email_verified_at": null,
    "created_at": "2026-11-24T01:46:03.000000Z",
    "updated_at": "2026-11-24T01:46:03.000000Z",
    "carrera_id": null,
    "sede_id": null,
    "user": {
        "id": 1,
        "name": "Persona Dos",
        "email": "p2@academica.test",
        "email_verified_at": null,
        "created_at": "2026-11-24T01:46:03.000000Z",
        "updated_at": "2026-11-24T01:46:03.000000Z",
        "carrera_id": null,
        "sede_id": null
    }
}
```

---

### 4.6. Cierre de Sesión (`POST /api/logout`) - 200 OK

Petición HTTP para revocación del token de acceso actual:
```bash
curl -i -X POST http://127.0.0.1:8000/api/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 2|t6X7nKM8QUoNC9hO7fSFbVDg3hIpLvxT6dufsoAq1124e78d"
```

**Respuesta HTTP:**
```http
HTTP/1.1 200 OK
Content-Type: application/json
```
```json
{
    "message": "Sesión cerrada exitosamente"
}
```

---

### 4.7. Consulta de Perfil con Token Revocado (`GET /api/me`) - 401 Unauthorized

Petición posterior al logout utilizando el mismo token:
```bash
curl -i -X GET http://127.0.0.1:8000/api/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 2|t6X7nKM8QUoNC9hO7fSFbVDg3hIpLvxT6dufsoAq1124e78d"
```

**Respuesta HTTP:**
```http
HTTP/1.1 401 Unauthorized
Content-Type: application/json
```
```json
{
    "message": "Unauthenticated."
}
```

---

## 5. Colección de Postman y Bruno

Los archivos ejecutables para importar las peticiones y pruebas automatizadas se encuentran en:
- `docs/evidencias/p2/autenticacion_postman_collection.json`
- `docs/evidencias/p2/bruno/`
  - `bruno.json`
  - `Register.bru`
  - `Login.bru`
  - `Login-Invalido.bru`
  - `Me-Con-Token.bru`
  - `Me-Sin-Token.bru`
  - `Logout.bru`
