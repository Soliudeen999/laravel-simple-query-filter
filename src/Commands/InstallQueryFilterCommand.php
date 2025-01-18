<?php

namespace Soliudeen999\QueryFilter\Commands;

use Illuminate\Console\Command;

class InstallQueryFilterCommand extends Command
{
    protected $signature = 'query-filter:install';

    protected $description = 'Install the query filter package';

    public function handle()
    {
        $this->info('Installing Laravel Simple Query Filter...');
        
        // Future implementation for any necessary installations
        
        $this->info('Installation completed successfully!');
    }
} 