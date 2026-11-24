<?php

namespace App\Enums;

enum Rol: string
{
    case Administrador = 'administrador';
    case Profesor = 'profesor';
    case Estudiante = 'estudiante';
    case Coordinador = 'coordinador';
}
