<?php

namespace Goldmangroup\Sberbank\Providers;

use Illuminate\Support\ServiceProvider;

class SberbankServiceProvider extends ServiceProvider
{
    public function boot()
    {
        include __DIR__ . '/../Http/routes.php';
    }

    public function register()
    {
        $this->registerConfig();
    }

    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/paymentmethods.php', 'paymentmethods'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/system.php', 'core'
        );
    }
}
