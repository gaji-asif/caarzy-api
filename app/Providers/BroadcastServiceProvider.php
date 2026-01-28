<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    

    public function boot(): void
{
    Broadcast::routes(['middleware' => ['auth:sanctum']]); // 👈 or 'auth' if not using Sanctum

    require base_path('routes/channels.php');
}
}
