<?php

namespace Readybytes\EnvChecker;

use Illuminate\Support\ServiceProvider;
use Readybytes\EnvChecker\Console\Commands\EnvCheckCommand;

class EnvCheckServiceProvider extends ServiceProvider
{
    
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                EnvCheckCommand::class,
            ]);
        }
    }

    public function register()
    {

    }

    
}