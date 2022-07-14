<?php

namespace Yggdrasill\GlobalConfig\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'global-config:init';

    protected $description = 'Install the Global Config package migrate / route / etc.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
    }
}
