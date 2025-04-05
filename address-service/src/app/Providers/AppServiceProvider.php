<?php

namespace App\Providers;

use App\Domains\Address\Repositories\AddressRepositoryInterface;
use App\Domains\Address\Repositories\EloquentAddressRepository;
use App\Services\AuthServiceClient;
use App\Services\RabbitMQService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(
            AddressRepositoryInterface::class,
            EloquentAddressRepository::class
        );

        $this->app->singleton(RabbitMQService::class, function ($app) {
            return new RabbitMQService();
        });

        $this->app->singleton(AuthServiceClient::class, function ($app) {
            return new AuthServiceClient($app->make(RabbitMQService::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
