<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * Solo administradores pueden listar usuarios.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user): Response
    {
        return $user->hasRole('administrador')
            ? Response::allow() // 200
            : Response::deny('No tiene permiso para listar usuarios.', 403);
    }

    /**
     * Determine whether the user can view the model.
     * ABAC: un usuario puede verse a sí mismo; admin puede ver a cualquiera.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function view(User $user, User $model): Response
    {
        if ($user->id === $model->id || $user->hasRole('administrador')) {
            return Response::allow(); // 200
        }

        return Response::deny('No tiene permiso para ver este usuario.', 403);
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
            : Response::deny('No tiene permiso para crear usuarios.', 403);
    }

    /**
     * Determine whether the user can update the model.
     * ABAC: un usuario puede actualizarse a sí mismo; admin puede actualizar a cualquiera.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function update(User $user, User $model): Response
    {
        if ($user->id === $model->id || $user->hasRole('administrador')) {
            return Response::allow(); // 200
        }

        return Response::deny('No tiene permiso para actualizar este usuario.', 403);
    }

    /**
     * Determine whether the user can delete the model.
     * Solo administrador (y no puede eliminarse a sí mismo).
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function delete(User $user, User $model): Response
    {
        if ($user->hasRole('administrador') && $user->id !== $model->id) {
            return Response::allow(); // 204
        }

        return Response::deny('No tiene permiso para eliminar este usuario.', 403);
    }
}
