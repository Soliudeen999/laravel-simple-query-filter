<?php

namespace Soliudeen999\QueryFilter\Providers;

use Illuminate\Support\ServiceProvider;
use Soliudeen999\QueryFilter\Commands\InstallQueryFilterCommand;

class QueryFilterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallQueryFilterCommand::class,
            ]);
        }
    }

    public function register()
    {
        //
    }
}
