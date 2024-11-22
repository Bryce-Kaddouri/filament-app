<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Google\Cloud\Core\ServiceBuilder;

class AppServiceProvider extends ServiceProvider
{


    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ServiceBuilder::class, function () {
            try {
                dd(env('GOOGLE_APPLICATION_CREDENTIALS'));
                return new ServiceBuilder([
                    'keyFilePath' => env('GOOGLE_APPLICATION_CREDENTIALS'),
                    'projectId' => env('GOOGLE_PROJECT_ID'),
                ]);
            } catch (\Exception $e) {
                dd($e);
                // Log the error or handle it as needed
                throw new \RuntimeException('Failed to initialize Google ServiceBuilder: ' . $e->getMessage());
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
    }
}
