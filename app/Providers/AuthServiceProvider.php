<?php

namespace App\Providers;

use App\Models\Carrera;
use App\Models\Estudiante;
use App\Models\Sede;
use App\Models\User;
use App\Policies\CarreraPolicy;
use App\Policies\EstudiantePolicy;
use App\Policies\SedePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Estudiante::class => EstudiantePolicy::class,
        Carrera::class    => CarreraPolicy::class,
        Sede::class       => SedePolicy::class,
        User::class       => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
