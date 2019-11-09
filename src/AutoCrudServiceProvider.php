<?php

namespace Pyxeel\AutoCrud;

use Illuminate\Support\ServiceProvider;
use Pyxeel\AutoCrud\Console\Commands\AutoCrudCommand;

class AutoCrudServiceProvider extends ServiceProvider
{
    /**
     * Array of commands
     *
     * @var array
     */
    protected $commands = [
        AutoCrudCommand::class
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
}
