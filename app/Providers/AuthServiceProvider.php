<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
        if (! $this->app->routesAreCached()) {
            Passport::routes();
        }
        
        Passport::tokensExpireIn(now()->addDays(90));
        Passport::refreshTokensExpireIn(now()->addDays(90));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
