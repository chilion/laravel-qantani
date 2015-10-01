<?php

namespace CJSDevelopment;

use Illuminate\Support\ServiceProvider;

class QantaniServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        $this->publishes([
            __DIR__.'/config/qantani.php' => config_path('qantani.php')
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        //
    }
}