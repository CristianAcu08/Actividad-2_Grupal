<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sede;
use Illuminate\Auth\Access\Response;

class SedePolicy
{
    /**
     * Determine whether the user can view any models.
     * Cualquier usuario autenticado puede listar sedes.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user): Response
    {
        return Response::allow(); // 200
    }

    /**
     * Determine whether the user can view the model.
     * ABAC: todos pueden ver su propia sede; admin ve cualquiera.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function view(User $user, Sede $sede): Response
    {
        if ($user->hasRole('administrador') || $user->sede_id === $sede->id) {
            return Response::allow(); // 200
        }

        return Response::deny('No tiene permiso para ver esta sede.', 403);
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
            : Response::deny('No tiene permiso para crear sedes.', 403);
    }

    /**
     * Determine whether the user can update the model.
     * Solo administrador.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function update(User $user, Sede $sede): Response
    {
        return $user->hasRole('administrador')
            ? Response::allow() // 200
            : Response::deny('No tiene permiso para actualizar sedes.', 403);
    }

    /**
     * Determine whether the user can delete the model.
     * Solo administrador.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Sede $sede): Response
    {
        return $user->hasRole('administrador')
            ? Response::allow() // 204
            : Response::deny('No tiene permiso para eliminar sedes.', 403);
    }
}
