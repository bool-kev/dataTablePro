<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Enregistrer les repositories
        $this->app->bind(
            \App\Repositories\ImportedDataRepository::class,
            function ($app) {
                return new \App\Repositories\ImportedDataRepository(
                    $app->make(\App\Models\ImportedData::class)
                );
            }
        );

        $this->app->bind(
            \App\Repositories\ImportHistoryRepository::class,
            function ($app) {
                return new \App\Repositories\ImportHistoryRepository(
                    $app->make(\App\Models\ImportHistory::class)
                );
            }
        );

        $this->app->bind(
            \App\Repositories\WorkspaceRepository::class,
            function ($app) {
                return new \App\Repositories\WorkspaceRepository(
                    $app->make(\App\Models\Workspace::class)
                );
            }
        );

        // Enregistrer les services
        $this->app->bind(
            \App\Services\ImportService::class,
            function ($app) {
                return new \App\Services\ImportService(
                    $app->make(\App\Repositories\ImportedDataRepository::class),
                    $app->make(\App\Repositories\ImportHistoryRepository::class)
                );
            }
        );

        $this->app->bind(
            \App\Services\ExportService::class,
            function ($app) {
                return new \App\Services\ExportService(
                    $app->make(\App\Repositories\ImportedDataRepository::class)
                );
            }
        );

        $this->app->bind(
            \App\Services\WorkspaceService::class,
            function ($app) {
                return new \App\Services\WorkspaceService(
                    $app->make(\App\Repositories\WorkspaceRepository::class)
                );
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
