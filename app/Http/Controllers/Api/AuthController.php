<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Registro de nuevo usuario en la plataforma académica.
     * El registro nunca acepta rol, roles ni permisos del cuerpo de la petición.
     */
    public function register(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'carrera_id' => ['nullable', 'integer', 'exists:carreras,id'],
            'sede_id' => ['nullable', 'integer', 'exists:sedes,id'],
        ]);

        // Se extraen únicamente los campos permitidos para prevenir asignación masiva
        // y escalada de privilegios (rol, roles, permisos son ignorados estrictamente).
        $usuario = User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => Hash::make($datos['password']),
            'carrera_id' => $datos['carrera_id'] ?? null,
            'sede_id' => $datos['sede_id'] ?? null,
        ]);

        $token = $usuario->createToken('api-academica')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'token' => $token,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $usuario,
        ], 201);
    }

    /**
     * Inicio de sesión y emisión de token Sanctum.
     */
    public function login(Request $request): JsonResponse
    {
        $credenciales = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $usuario = User::where('email', $credenciales['email'])->first();

        if (! $usuario || ! Hash::check($credenciales['password'], $usuario->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        $token = $usuario->createToken('api-academica')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'token' => $token,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $usuario,
        ], 200);
    }

    /**
     * Obtener los datos del usuario autenticado.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $usuario */
        $usuario = $request->user();

        $respuesta = $usuario->toArray();
        $respuesta['user'] = $usuario;

        return response()->json($respuesta, 200);
    }

    /**
     * Cerrar sesión invalidando el token de acceso actual.
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Sesión cerrada exitosamente',
        ], 200);
    }
}
