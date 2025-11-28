<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes;

        // Optional Configure token lifetimes
        // Passport::tokenExpireIn(now()->addDays(15));
        // Passport::refreshTokenExpireIn(now()->addDays(30));
        // Passport::personalAccessTokenExpireIn(now()->addMonth(6));
    }
}