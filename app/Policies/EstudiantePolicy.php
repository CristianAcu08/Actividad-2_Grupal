<?php

namespace App\Policies;

use App\Enums\Permiso;
use App\Enums\Rol;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EstudiantePolicy
{
    /**
     * Determina si el usuario puede consultar el listado general de estudiantes.
     */
    public function viewAny(User $user): Response
    {
        return $user->can(Permiso::Ver->value)
            ? Response::allow()
            : Response::deny('No tiene permiso para listar estudiantes.', 403);
    }

    /**
     * Determina si el usuario puede consultar un estudiante específico.
     * - Administrador: cualquier registro.
     * - Estudiante: únicamente su propio registro (nunca registros ajenos, activos o inactivos).
     * - Profesor / Coordinador: registros activos de su misma carrera y sede (Actividad 2 + ABAC).
     */
    public function view(User $user, Estudiante $estudiante): Response
    {
        if (! $user->can(Permiso::Ver->value)) {
            return Response::deny('No tiene permiso para consultar estudiantes.', 403);
        }

        if ($user->hasRole(Rol::Administrador->value)) {
            return Response::allow();
        }

        if ($user->hasRole(Rol::Estudiante->value)) {
            return $estudiante->user_id === $user->id
                ? Response::allow()
                : Response::deny('Solo puede consultar su propio registro estudiantil.', 403);
        }

        if ($user->hasAnyRole([Rol::Profesor->value, Rol::Coordinador->value])) {
            $sedeEstudianteId = $estudiante->carrera?->sede_id;

            $autorizado = $estudiante->activo === true
                && $user->carrera_id !== null
                && $user->carrera_id === $estudiante->carrera_id
                && $user->sede_id !== null
                && $user->sede_id === $sedeEstudianteId;

            return $autorizado
                ? Response::allow()
                : Response::deny('No tiene permiso para ver este estudiante.', 403);
        }

        return Response::deny('No tiene permiso para ver este estudiante.', 403);
    }

    /**
     * Determina si el usuario puede crear estudiantes (RBAC: estudiantes.crear).
     */
    public function create(User $user): Response
    {
        return $user->can(Permiso::Crear->value)
            ? Response::allow()
            : Response::deny('No tiene permiso para crear estudiantes.', 403);
    }

    /**
     * Determina si el usuario puede actualizar un estudiante.
     * - Administrador: con permiso estudiantes.editar.
     * - Profesor / Coordinador: con permiso estudiantes.editar + ABAC (misma carrera y sede).
     */
    public function update(User $user, Estudiante $estudiante): Response
    {
        if (! $user->can(Permiso::Editar->value)) {
            return Response::deny('No tiene permiso para actualizar estudiantes.', 403);
        }

        if ($user->hasRole(Rol::Administrador->value)) {
            return Response::allow();
        }

        if ($user->hasAnyRole([Rol::Profesor->value, Rol::Coordinador->value])) {
            $sedeEstudianteId = $estudiante->carrera?->sede_id;

            $autorizado = $user->carrera_id !== null
                && $user->carrera_id === $estudiante->carrera_id
                && $user->sede_id !== null
                && $user->sede_id === $sedeEstudianteId;

            return $autorizado
                ? Response::allow()
                : Response::deny('No tiene permiso para actualizar este estudiante.', 403);
        }

        return Response::deny('No tiene permiso para actualizar este estudiante.', 403);
    }

    /**
     * Determina si el usuario puede eliminar estudiantes (RBAC: estudiantes.eliminar).
     */
    public function delete(User $user, Estudiante $estudiante): Response
    {
        return $user->can(Permiso::Eliminar->value)
            ? Response::allow()
            : Response::deny('No tiene permiso para eliminar estudiantes.', 403);
    }
}
