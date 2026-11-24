<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

foreach (['auth', 'dashboard', 'estudiantes'] as $modulo) {
    $archivo = __DIR__.'/api/'.$modulo.'.php';

    if (file_exists($archivo)) {
        require $archivo;
    }
}
