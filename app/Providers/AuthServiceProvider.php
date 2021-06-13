<?php

namespace App\Providers;

use App\RoleEnum;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('access-hub', function($user) {
            return $user->hasRole(RoleEnum::Member);
        });

        Gate::define('test-missions', function($user) {
            return $user->hasRole(RoleEnum::Tester);
        });

        Gate::define('manage-operations', function($user) {
            return $user->hasRole(RoleEnum::Tester);
        });

        Gate::define('manage-users', function($user) {
            return $user->hasRole(RoleEnum::Staff);
        });

        Gate::define('view-applications', function($user) {
            return $user->hasRole(RoleEnum::Staff);
        });

        Gate::define('view-absences', function($user) {
            return $user->hasRole(RoleEnum::Staff);
        });

        Gate::define('send-emails', function($user) {
            return $user->hasRole(RoleEnum::Staff);
        });
    }
}
