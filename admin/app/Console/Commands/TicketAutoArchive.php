<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TicketAutoArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:autoarchive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ticket Auto Archive';

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
        $ticket_history = \App\TicketStatusHistory::where('status','Closed')->with('ticket')->orderBy('created_at','desc')->groupBy('ticket_id')->get();
        foreach ($ticket_history as $key => $history) {
            $date_now = date('Y-m-d');
            $status_date = date('Y-m-d',strtotime($history->created_at));
            
            $date2=date_create($date_now);
            $date1=date_create($status_date);
            $diff=date_diff($date1,$date2);
            $days_diff = $diff->format("%a");

            if($days_diff >= 3) {
                $history->ticket->ticket_status = 'Archived';
                $history->ticket->save();

                $ticket_status_history = \App\TicketStatusHistory::create([
                    'ticket_id' => $history->ticket_id,
                    'user_id' => 0,
                    'status' => 'Archived'
                ]);
            }


            \Log::info('Cron: Auto Archive Working');
        }
    }
}
