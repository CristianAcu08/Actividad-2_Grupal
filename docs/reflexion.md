# Actividad 5: Reflexión de Seguridad

**Curso:** Tecnologías y Sistemas Web III (ITI-621)  
**Tema:** Seguridad y control de acceso en servicios web (Laravel 13, Sanctum, Gates, Policies, RBAC y ABAC)  
**Responsable:** Persona 5 (P5) — Pruebas, colección y entrega final  

---

## 1. ¿Cuál es la diferencia entre autenticación y autorización?

La **autenticación** verifica *quién* es el sujeto que realiza la solicitud, comprobando su identidad mediante credenciales (por ejemplo, correo y contraseña en `POST /api/login`) o un token previamente emitido (Bearer Token de **Laravel Sanctum** en el middleware `auth:sanctum`).

La **autorización**, en cambio, ocurre *después* de autenticar al usuario y determina *qué acciones puede ejecutar* sobre qué recursos específicos (por ejemplo, si el usuario puede acceder a `/api/admin/dashboard` mediante un Gate, si tiene el permiso `estudiantes.crear` mediante Spatie RBAC, o si puede editar un registro concreto en `EstudiantePolicy` según su carrera y sede).

En resumen: **autenticación = identidad**; **autorización = permisos y reglas de negocio**.

---

## 2. ¿En qué casos utilizaría Gates?

Los **Gates** son ideales para reglas de autorización **generales o transversales** que no dependen necesariamente de una instancia concreta de un modelo Eloquent. Por ejemplo:

- Controlar el acceso a secciones o paneles administrativos (`access-admin-dashboard`, `access-professor-dashboard`).
- Autorizar acciones globales del sistema como exportar reportes generales, ver métricas institucionales o ejecutar tareas de mantenimiento.
- Evaluar capacidades rápidas basadas en el rol o atributos del usuario autenticado sin necesidad de asociar un recurso CRUD.

---

## 3. ¿Por qué las Policies son adecuadas para operaciones CRUD?

Las **Policies** organizan la lógica de autorización alrededor de un **modelo específico** (como `Estudiante` con `EstudiantePolicy`), mapeando directamente cada operación estándar del ciclo de vida CRUD a un método con semántica clara:

- `viewAny`: listar la colección completa (`index`).
- `view`: consultar un registro puntual (`show`).
- `create`: crear nuevos registros (`store`).
- `update`: modificar una instancia existente (`update`).
- `delete`: eliminar una instancia existente (`destroy`).

Esto evita duplicar condicionales en los controladores, facilita el descubrimiento automático de Laravel, mantiene el código cohesivo y permite combinar permisos globales con validaciones de propiedad o contexto sobre el registro recibido.

---

## 4. ¿Cuál es la diferencia entre RBAC y ABAC?

- **RBAC (Role-Based Access Control):** toma decisiones de acceso basándose únicamente en los **roles** asignados al usuario y los **permisos** vinculados a esos roles (implementado con *Spatie Laravel Permission*, por ejemplo: `administrador`, `profesor`, `coordinador`, `estudiante` y permisos `estudiantes.ver`, `estudiantes.crear`, `estudiantes.editar`, `estudiantes.eliminar`). Es simple y fácil de auditar para reglas amplias, pero insuficiente cuando el permiso depende de datos dinámicos.
- **ABAC (Attribute-Based Access Control):** evalúa **atributos** del usuario, del recurso y del contexto en tiempo de ejecución. En este taller, aunque un `profesor` o `coordinador` posea el permiso RBAC `estudiantes.editar`, ABAC exige además que `$user->carrera_id === $estudiante->carrera_id` y `$user->sede_id === $estudiante->carrera->sede_id`, y para consultas restringe registros inactivos (`activo = false`).

Combinar ambos modelos ofrece defensa en profundidad: RBAC filtra capacidades gruesas y ABAC aplica granularidad por dominio y contexto.

---

## 5. ¿Por qué un permiso `estudiantes.editar` no debería permitir modificar cualquier estudiante?

Porque tener la **capacidad funcional** de editar estudiantes no implica tener **jurisdicción** sobre todos los registros de la universidad. Si el sistema solo comprobara `$user->can('estudiantes.editar')`, un profesor de la carrera ITI en Sede Central podría alterar expedientes de estudiantes de ISW o de una Sede Regional, incurriendo en una vulnerabilidad de **Broken Object Level Authorization (BOLA / IDOR)**, la principal falla en el OWASP API Security Top 10.

Al complementar el permiso con reglas de propiedad y atributos (carrera y sede), se aplica el **principio de mínimo privilegio**: cada usuario modifica únicamente los expedientes que le corresponden por responsabilidad académica.

---

## 6. ¿Qué riesgo existe si el registro público permite enviar el rol del usuario?

Permitir que el cliente envíe `rol`, `roles` o `permissions` en `POST /api/register` introduce una vulnerabilidad crítica de **Asignación Masiva (Mass Assignment) y Escalada de Privilegios**. Cualquier atacante podría enviar una petición `POST` con `"roles": ["administrador"]` y obtener control total sobre la API sin intervención administrativa, pudiendo consultar, modificar o eliminar todos los registros académicos.

Por eso `AuthController::register` debe validar y extraer explícitamente únicamente los campos permitidos (`name`, `email`, `password`, y opcionalmente `carrera_id`/`sede_id`), ignorando cualquier atributo de rol o permiso en el cuerpo JSON y delegando la asignación de privilegios a procesos internos controlados.

---

## 7. ¿Por qué la autorización debe realizarse en el backend?

Porque el frontend (navegador, app móvil o cliente SPA) corre bajo el control total del usuario y **no es un entorno confiable**. Ocultar un botón en la interfaz o restringir una ruta en JavaScript mejora la experiencia de usuario, pero no aporta seguridad real: cualquier persona puede usar `curl`, Postman, Bruno o DevTools para invocar directamente `POST /api/estudiantes` o `DELETE /api/estudiantes/1` con un token válido.

El backend es el único punto de control confiable que recibe todas las solicitudes antes de acceder a la base de datos; por ello, toda verificación de autenticación (`auth:sanctum`), Gates, middleware de permisos y Policies/ABAC debe aplicarse obligatoriamente en el servidor y verificarse mediante pruebas automatizadas de Feature.

---

## 8. ¿Qué diferencia existe entre HTTP 401 y HTTP 403?

| Aspecto | `401 Unauthorized` | `403 Forbidden` |
|---|---|---|
| **Significado real** | **No autenticado.** El servidor no conoce la identidad del cliente porque falta el token Bearer, está mal formado, expiró o fue revocado (`/api/logout`). | **Autenticado, pero sin autorización.** El servidor sí verificó la identidad del usuario, pero sus roles, permisos (RBAC) o atributos (ABAC) impiden ejecutar la acción solicitada. |
| **Capa que lo genera en Laravel** | Middleware `auth:sanctum` (o credenciales inválidas en `login`). | `Gate::authorize()`, `EstudiantePolicy` o middleware `permission:estudiantes.crear` de Spatie. |
| **Cómo se resuelve** | Enviando credenciales válidas o un token activo en `Authorization: Bearer <token>`. | No basta con re-autenticarse; el usuario requiere un rol, permiso o relación de atributos válida para el recurso. |
| **Ejemplo en la API** | `GET /api/me` o `GET /api/admin/dashboard` sin encabezado `Authorization`. | `GET /api/admin/dashboard` con token de `profesor`, o `PUT /api/estudiantes/{luis}` con token de `profesor.iti@academica.test`. |
