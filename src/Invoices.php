<?php

// This file is part of eavio/invoices.

namespace eavio\invoices\src;

use Illuminate\Support\ServiceProvider;

// This is the Invoices class.
class Invoices extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/Templates', 'invoices');

        $this->publishes([
            __DIR__.'/Templates'           => resource_path('views/vendor/invoices'),
            __DIR__.'/Config/invoices.php' => config_path('invoices.php'),
        ], 'invoices');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/invoices.php', 'invoices'
        );
    }
}
