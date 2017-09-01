<?php
/**
 * This file is part of Jrean\SuperCollege package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\SuperCollege;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SuperCollegeServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // config
        $this->publishes([
            __DIR__ . '/config/supercollege.php' => config_path('supercollege.php')
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerSuperCollege($this->app);

        // configurations
        $this->mergeConfigFrom(
            __DIR__ . '/config/supercollege.php', 'supercollege'
        );
    }

    /**
     * Register the supercollege.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    protected function registerSuperCollege(Application $app)
    {
        $app->bind('supercollege', function ($app) {
            return new SuperCollege;
        });

        $app->alias('supercollege', SuperCollege::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'supercollege',
        ];
    }
}
