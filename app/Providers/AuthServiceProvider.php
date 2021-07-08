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

        Gate::before(function ($user, $ability) {
            if ($user->hasRole(RoleEnum::Admin)) {
                return true;
            }
        });

        Gate::define('access-hub', function($user) {
            return $user->hasRole(RoleEnum::Member);
        });

        Gate::define('test-missions', function($user) {
            // Includes adding notes, downloading missions, 
            // read locked briefings, and see unverified missions 
            return $user->hasRole(RoleEnum::Tester);
        });

        Gate::define('verify-missions', function($user) {
            return $user->hasRole(RoleEnum::SeniorTester);
        });

        Gate::define('manage-missions', function($user) {
            // Includes updating and deleting missions
            return $user->hasRole(RoleEnum::Operations);
        });

        Gate::define('share-missions', function($user) {
            return $user->hasRole(RoleEnum::Staff);
        });

        Gate::define('manage-operations', function($user) {
            return $user->hasRole(RoleEnum::Operations);
        });

        Gate::define('view-users', function($user) {
            return $user->hasRole(RoleEnum::Staff);
        });

        Gate::define('view-applications', function($user) {
            return $user->hasRole(RoleEnum::Staff);
        });

        Gate::define('manage-applications', function($user) {
            // Admins only
        });

        Gate::define('delete-media', function($user) {
            return $user->hasRole(RoleEnum::Operations);
        });

        Gate::define('manage-public-media', function($user) {
            // Admins only
        });
    }
}
