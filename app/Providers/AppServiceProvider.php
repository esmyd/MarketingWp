<?php

namespace App\Providers;

use App\Services\PermissionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        Carbon::setLocale(config('app.locale', 'es'));

        Blade::if('perm', function (string $permission) {
            $user = auth()->user();

            return $user && app(PermissionService::class)->userCan($user, $permission);
        });

        view()->composer(['admin.*', 'admin.layouts.app'], function ($view) {
            $user = auth()->user();
            $view->with('canPerm', function (string $permission) use ($user) {
                return $user && app(PermissionService::class)->userCan($user, $permission);
            });
        });
    }
}
