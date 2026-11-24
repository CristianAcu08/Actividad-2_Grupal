<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Carrera;
use Illuminate\Auth\Access\Response;

class CarreraPolicy
{
    /**
     * Determine whether the user can view any models.
     * Cualquier usuario autenticado puede listar carreras.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user): Response
    {
        return Response::allow(); // 200
    }

    /**
     * Determine whether the user can view the model.
     * ABAC: admin ve cualquiera; profesor/coordinador solo de su misma sede.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function view(User $user, Carrera $carrera): Response
    {
        if ($user->hasRole('administrador')) {
            return Response::allow(); // 200
        }

        if ($user->hasAnyRole(['profesor', 'coordinador'])
            && $user->sede_id === $carrera->sede_id) {
            return Response::allow(); // 200
        }

        return Response::deny('No tiene permiso para ver esta carrera.', 403);
    }

    /**
     * Determine whether the user can create models.
     * Solo administrador.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function create(User $user): Response
    {
        return $user->hasRole('administrador')
            ? Response::allow() // 201
            : Response::deny('No tiene permiso para crear carreras.', 403);
    }

    /**
     * Determine whether the user can update the model.
     * ABAC: admin puede actualizar cualquiera; coordinador solo de su sede.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function update(User $user, Carrera $carrera): Response
    {
        if ($user->hasRole('administrador')) {
            return Response::allow(); // 200
        }

        if ($user->hasRole('coordinador')
            && $user->sede_id === $carrera->sede_id) {
            return Response::allow(); // 200
        }

        return Response::deny('No tiene permiso para actualizar esta carrera.', 403);
    }

    /**
     * Determine whether the user can delete the model.
     * Solo administrador.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Carrera $carrera): Response
    {
        return $user->hasRole('administrador')
            ? Response::allow() // 204
            : Response::deny('No tiene permiso para eliminar carreras.', 403);
    }
}
