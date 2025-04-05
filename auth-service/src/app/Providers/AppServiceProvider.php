<?php

namespace App\Providers;

use App\Domains\User\Repositories\EloquentUserRepository;
use App\Domains\User\Repositories\UserRepositoryInterface;
use App\Services\RabbitMQService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        $this->app->singleton(RabbitMQService::class, function ($app) {
            return new RabbitMQService();
        });
    }
}
