<?php

require __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

// 1. Generar salida Tinker
$tinkerOutput = "====================================================================\n";
$tinkerOutput .= " EVIDENCIA P3: VERIFICACION DESDE TINKER - ROLES Y PERMISOS (RBAC)\n";
$tinkerOutput .= "====================================================================\n\n";

$usuarios = [
    'admin@academica.test',
    'profesor.iti@academica.test',
    'profesor.isw@academica.test',
    'estudiante1@academica.test',
    'estudiante2@academica.test',
    'coordinador@academica.test',
];

foreach ($usuarios as $email) {
    $user = User::where('email', $email)->first();
    $roles = $user->getRoleNames()->toArray();
    $permisos = $user->getAllPermissions()->pluck('name')->toArray();
    $canCrear = $user->can('estudiantes.crear');
    $canEditar = $user->can('estudiantes.editar');
    $canVer = $user->can('estudiantes.ver');
    $canEliminar = $user->can('estudiantes.eliminar');
    $gateAdmin = Gate::forUser($user)->allows('access-admin-dashboard');
    $gateProf = Gate::forUser($user)->allows('access-professor-dashboard');

    $tinkerOutput .= ">>> \$user = App\\Models\\User::where('email', '{$email}')->first();\n";
    $tinkerOutput .= "=> App\\Models\\User {#id: {$user->id}, name: \"{$user->name}\", email: \"{$user->email}\"}\n\n";

    $tinkerOutput .= ">>> \$user->getRoleNames();\n";
    $tinkerOutput .= "=> " . json_encode($roles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    $tinkerOutput .= ">>> \$user->getAllPermissions()->pluck('name');\n";
    $tinkerOutput .= "=> " . json_encode($permisos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    $tinkerOutput .= ">>> \$user->can('estudiantes.crear');\n";
    $tinkerOutput .= "=> " . ($canCrear ? 'true' : 'false') . "\n\n";

    $tinkerOutput .= ">>> \$user->can('estudiantes.editar');\n";
    $tinkerOutput .= "=> " . ($canEditar ? 'true' : 'false') . "\n\n";

    $tinkerOutput .= ">>> Gate::forUser(\$user)->allows('access-admin-dashboard');\n";
    $tinkerOutput .= "=> " . ($gateAdmin ? 'true' : 'false') . "\n\n";

    $tinkerOutput .= ">>> Gate::forUser(\$user)->allows('access-professor-dashboard');\n";
    $tinkerOutput .= "=> " . ($gateProf ? 'true' : 'false') . "\n\n";
    $tinkerOutput .= "--------------------------------------------------------------------\n\n";
}

file_put_contents(__DIR__ . '/roles_permisos_tinker.txt', $tinkerOutput);

// 2. Matriz de Gates y Respuestas HTTP
$casos = [
    [
        'identificador' => 'sin_token',
        'descripcion' => 'Usuario no autenticado (Sin Token)',
        'token' => null,
    ],
    [
        'identificador' => 'admin',
        'descripcion' => 'Administrador General (admin@academica.test)',
        'email' => 'admin@academica.test',
    ],
    [
        'identificador' => 'profesor_iti',
        'descripcion' => 'Profesor de TI (profesor.iti@academica.test)',
        'email' => 'profesor.iti@academica.test',
    ],
    [
        'identificador' => 'profesor_isw',
        'descripcion' => 'Profesor de Software (profesor.isw@academica.test)',
        'email' => 'profesor.isw@academica.test',
    ],
    [
        'identificador' => 'estudiante_ana',
        'descripcion' => 'Ana Estudiante (estudiante1@academica.test)',
        'email' => 'estudiante1@academica.test',
    ],
    [
        'identificador' => 'estudiante_luis',
        'descripcion' => 'Luis Estudiante (estudiante2@academica.test)',
        'email' => 'estudiante2@academica.test',
    ],
    [
        'identificador' => 'coordinador',
        'descripcion' => 'Coordinador Académico (coordinador@academica.test)',
        'email' => 'coordinador@academica.test',
    ],
];

$endpoints = [
    '/api/admin/dashboard' => 'access-admin-dashboard',
    '/api/profesor/dashboard' => 'access-professor-dashboard',
];

$matrizResultados = [];

foreach ($casos as $caso) {
    $token = null;
    if (isset($caso['email'])) {
        $u = User::where('email', $caso['email'])->first();
        $token = $u->createToken('matriz-test')->plainTextToken;
    }

    $fila = [
        'caso' => $caso['descripcion'],
        'pruebas' => [],
    ];

    foreach ($endpoints as $endpoint => $gateNombre) {
        // Limpiar guard de autenticacion antes de cada request
        Auth::forgetGuards();

        $httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

        $server = [
            'HTTP_ACCEPT' => 'application/json',
        ];
        if ($token) {
            $server['HTTP_AUTHORIZATION'] = "Bearer {$token}";
        }

        $request = Request::create($endpoint, 'GET', [], [], [], $server);
        $response = $httpKernel->handle($request);
        $statusCode = $response->getStatusCode();
        $payload = json_decode($response->getContent(), true);

        if (isset($payload['trace'])) {
            unset($payload['trace']);
        }

        $fila['pruebas'][$endpoint] = [
            'gate' => $gateNombre,
            'status_code' => $statusCode,
            'response' => $payload,
        ];
    }

    $matrizResultados[] = $fila;
}

file_put_contents(
    __DIR__ . '/matriz_gates_dashboards.json',
    json_encode($matrizResultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "Archivos actualizados correctamente.\n";
