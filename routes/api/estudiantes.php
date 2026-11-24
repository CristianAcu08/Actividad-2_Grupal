<?php

use App\Enums\Permiso;
use App\Http\Controllers\Api\EstudianteController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/estudiantes', [EstudianteController::class, 'index']);

    Route::post('/estudiantes', [EstudianteController::class, 'store'])
        ->middleware('permission:'.Permiso::Crear->value);

    Route::get('/estudiantes/{estudiante}', [EstudianteController::class, 'show']);
    Route::put('/estudiantes/{estudiante}', [EstudianteController::class, 'update']);
    Route::patch('/estudiantes/{estudiante}', [EstudianteController::class, 'update']);
    Route::delete('/estudiantes/{estudiante}', [EstudianteController::class, 'destroy']);
});
