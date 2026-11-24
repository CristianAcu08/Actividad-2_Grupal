<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sede extends Model
{
    protected $fillable = [
        'nombre',
        'codigo',
    ];

    public function carreras(): HasMany
    {
        return $this->hasMany(Carrera::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
