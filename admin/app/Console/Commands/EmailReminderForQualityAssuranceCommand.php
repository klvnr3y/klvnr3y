<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EmailReminderForQualityAssuranceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailreminderforqac:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email Reminder For Quality Assurance Command';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        $interval = date_diff($datetime1, $datetime2);
        return $interval->format($differenceFormat);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $merchantName = "";
        $to_email = 'aja@posleader.com';
        $to_name = 'Aja POSLeader';
        // $to_email = 'joshuasaubon@gmail.com';
        // $to_name = 'Joshua Saubon';

        $formData = \App\FormData::where('notes','LIKE','%Quality Assurance Review%')->with('clearent_boardings')->get();

        foreach ($formData as $key => $data) {
            $notes = $data->notes;
                if (isset($data['clearent_boardings']['merchant'])){
                    $a = json_decode($data['clearent_boardings']['merchant']);
                    $merchantName = $a->dbaName;

                }else{
                    $a = json_decode($data['inputs']);
                    $merchantName = $a->DbaOfBusiness;

                }


            if(strpos($notes,'Clearent Application Submitted') === false) {
                if(strpos($notes,'Email Reminder Sent') === false) {
                    // dd($notes);
                    $notes = explode('<br/>',$notes);
                    // dd($notes);
                    foreach ($notes as $key => $note) {
                        if(strpos($note,'Quality Assurance Review: ') !== false) {
                            $date = str_replace('Quality Assurance Review: ','',$note);
                            $date_diff = $this->dateDifference($date, date('Y-m-d H:i:s'));
                            if($date_diff > 0) {
                                // dd($date,date('Y-m-d H:i:s',strtotime($date)), dateDifference($date, date('Y-m-d H:i:s')));
                                // TRELLO CARD https://trello.com/c/LL7vQUOq/353-once-app-signed-and-gone-to-qa-after-24-hours-of-it-not-submitted-get-email-reminder-to-aja-that-it-needs-to-be-submitted
                                // SEND EMAIL
                                $send = array(
                                    'to_name' => $to_name,
                                    'to_email' => $to_email,
                                    'subject' => 'Boarding App Reminder - Quality Assurance Review',
                                    'cc'=>'cdebaca.jesse@gmail.com',
                                    'from_name' => env('MAIL_FROM_NAME'),
                                    'from_email' => env('MAIL_FROM_ADDRESS'),
                                    'template' => 'admin.emails.emailRemidner', // alisdi ug template
                                    'body_data' => [
                                        'name' => $merchantName, // KUHAA ANG MERCHANT NAME
                                    ]
                                );

                                event(new \App\Events\SendMailEvent($send));

                                // SAVE NOTES
                                $data->notes = $data->notes.'<br/> Email Reminder Sent: '.date('Y-m-d H:i:s');
                                $data->save();
                            }
                        }
                    }
                }

            };
        }
        return 0;
    }
}
