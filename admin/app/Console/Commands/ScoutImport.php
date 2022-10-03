<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScoutImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ScoutImport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Elastic Search Data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        try {
            
            // $this->call('elastic:create-index "App\\\Scout\\\FormDataIndexConfigurator"');
            $this->call('scout:import',["model" => "App\\FormData"]);
            // $this->call('elastic:create-index "App\\\Scout\\\FormIndexConfigurator"');
            $this->call('scout:import',["model" => "App\\\Form"]);
            // $this->call('elastic:create-index "App\\\Scout\\\ClearentBoardingIndexConfigurator"');
            $this->call('scout:import',["model" => "App\\\ClearentBoarding"]);
            // $this->call('elastic:create-index "App\\\Scout\\\TicketIndexConfigurator"');
            $this->call('scout:import',["model" => "App\\\Ticket"]);
            // $this->call('elastic:create-index "App\\\Scout\\\UserAccountLinkIndexConfigurator"');
            $this->call('scout:import',["model" => "App\\\UserAccountLink"]);
            // $this->call('elastic:create-index "App\\\Scout\\\UserIndexConfigurator"');
            $this->call('scout:import',["model" => "App\\\User"]);
            \Log::info("CRON Elastic Import Success!");
            echo "CRON Elastic Import Success!";
        } catch (\Throwable $th) {
            \Log::info("CRON Elastic Import ERROR!");
            echo "CRON Elastic Import ERROR!";
        }
        
        return 0;
    }
}
