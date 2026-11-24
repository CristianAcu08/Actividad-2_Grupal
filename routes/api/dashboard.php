<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/dashboard', function (Request $request) {
        Gate::authorize('access-admin-dashboard');

        return response()->json([
            'message' => 'Bienvenido al dashboard administrativo',
            'user' => $request->user()->name,
        ]);
    });

    Route::get('/profesor/dashboard', function (Request $request) {
        Gate::authorize('access-professor-dashboard');

        return response()->json([
            'message' => 'Bienvenido al dashboard de profesores',
            'user' => $request->user()->name,
        ]);
    });
});
