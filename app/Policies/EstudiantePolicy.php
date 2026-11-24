<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Estudiante;
use Illuminate\Auth\Access\Response;

class EstudiantePolicy
{
    /**
     * Determine whether the user can view any models.
     * Roles permitidos: administrador, profesor, coordinador.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user): Response
    {
        return $user->hasAnyRole(['administrador', 'profesor', 'coordinador'])
            ? Response::allow()
            : Response::deny('No tiene permiso para listar estudiantes.', 403);
    }

    /**
     * Determine whether the user can view the model.
     * ABAC: mismo estudiante, o admin/profesor de la misma carrera.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function view(User $user, Estudiante $estudiante): Response
    {
        // Admin puede ver cualquier estudiante
        if ($user->hasRole('administrador')) {
            return Response::allow(); // 200
        }

        // Profesor/Coordinador solo de su misma carrera
        if ($user->hasAnyRole(['profesor', 'coordinador'])
            && $user->carrera_id === $estudiante->carrera_id) {
            return Response::allow(); // 200
        }

        return Response::deny('No tiene permiso para ver este estudiante.', 403);
    }

    /**
     * Determine whether the user can create models.
     * Solo administrador y coordinador.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function create(User $user): Response
    {
        return $user->hasAnyRole(['administrador', 'coordinador'])
            ? Response::allow() // 201
            : Response::deny('No tiene permiso para crear estudiantes.', 403);
    }

    /**
     * Determine whether the user can update the model.
     * ABAC: admin puede actualizar cualquiera; coordinador solo de su sede.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function update(User $user, Estudiante $estudiante): Response
    {
        if ($user->hasRole('administrador')) {
            return Response::allow(); // 200
        }

        if ($user->hasRole('coordinador')
            && $user->sede_id === $estudiante->sede_id) {
            return Response::allow(); // 200
        }

        return Response::deny('No tiene permiso para actualizar este estudiante.', 403);
    }

    /**
     * Determine whether the user can delete the model.
     * Solo administrador.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Estudiante $estudiante): Response
    {
        return $user->hasRole('administrador')
            ? Response::allow() // 204
            : Response::deny('No tiene permiso para eliminar estudiantes.', 403);
    }
}
