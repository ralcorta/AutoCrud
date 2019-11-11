<?php

namespace Pyxeel\AutoCrud;

use Illuminate\Support\ServiceProvider;
use Pyxeel\AutoCrud\Console\Commands\AutoCrudInitCommand;
use Pyxeel\AutoCrud\Console\Commands\AutoCrudCompleteCommand;

class AutoCrudServiceProvider extends ServiceProvider
{
    /**
     * Array of commands
     *
     * @var array
     */
    protected $commands = [
        AutoCrudInitCommand::class,
        AutoCrudCompleteCommand::class
    ];

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/AutoCrud.php' => config_path('AutoCrud.php')
        ], 'config');
    }
}
