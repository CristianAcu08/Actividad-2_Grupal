<?php

namespace App\Enums;

enum Permiso: string
{
    case Ver = 'estudiantes.ver';
    case Crear = 'estudiantes.crear';
    case Editar = 'estudiantes.editar';
    case Eliminar = 'estudiantes.eliminar';
}
