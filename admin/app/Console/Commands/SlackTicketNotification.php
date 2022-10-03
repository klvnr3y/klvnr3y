<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use JoliCode\Slack\ClientFactory;
use JoliCode\Slack\Exception\SlackErrorResponse;
use DateTime;
class SlackTicketNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slack:ticket_notif';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Slack Ticket Notification for Awaiting Support Reply ';

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
        $tickets = \App\Ticket::select([\DB::raw("DATEDIFF(CURRENT_TIMESTAMP(), status_change_updated_at) as `date_diff`"),'tickets.*'])->where('ticket_status','Awaiting Support Reply')->where('status_change_updated_at','<',date('Y-m-d H:i:s', strtotime('-1 day')))->get();
        // dd($ticket);

        // FOR POSPAY
        foreach ($tickets as $key => $ticket) {
            $this->slackTicketNotif($ticket);
        }

        // FOR DEV
        foreach ($tickets as $key => $ticket) {
            $this->slackTicketNotifSB($ticket);
        }
    }

    public function getTimeDiff($ticket) {
        $datetime1 = new DateTime(date('Y-m-d H:i:s'));//start time
        $datetime2 = new DateTime($ticket->status_change_updated_at);//end time
        $interval = $datetime1->diff($datetime2);
        // $raw_format = $interval->format('%M-%D %H:%I:%S');
        // $format = date('m-d-h:i a',strtotime('2000-'.$raw_format));
        $days = $interval->d;
        if($days > 0) {
            $days = $days * 24;
        }
        $hours = $days+$interval->h;
        $minutes = $interval->i;
        
        $format = $hours."h and ".$minutes."m";
        return $format;
    }
    public function slackTicketNotif($ticket) {
        $client = ClientFactory::create(env('SLACK_API_KEY'));

        try {
            $subject = $ticket->ticket_subject;
            $subject = str_replace('Request for','',$subject);
            // This method requires your token to have the scope "chat:write"
            $time_diff = $this->getTimeDiff($ticket);
            $response = $client->chatPostMessage([ 
                'channel' => 'ticket-alerts',
                'text' => 'Ticket '.$subject.' has been in Awaiting for Support Reply for '.$time_diff, 
                'blocks' => json_encode([
                    [
                        "type" => "section",
                        "text" => [
                            "type" => "plain_text",
                            "text" => 'Ticket '.$subject.' has been in Awaiting for Support Reply for '.$time_diff,
                            "emoji" => true
                        ]
                    ],
                    [
                        "type" => "actions",
                        "elements" => [
                            [
                                "type" => "button",
                                "text" => [
                                    "type" => "plain_text",
                                    "emoji" => true,
                                    "text" => "Visit"
                                ],
                                "url" => url("/tickets/ticket/".$ticket->id."")
                            ],
                            [
                                "type" => "button",
                                "text" => [
                                    "type" => "plain_text",
                                    "emoji" => true,
                                    "text" => "Mark as Resolved"
                                ],
                                "style" => "primary",
                                "url" => url("/tickets/ticket/".$ticket->id."?status=Closed")
                            ],
                            [
                                "type" => "button",
                                "text" => [
                                    "type" => "plain_text",
                                    "emoji" => true,
                                    "text" => "Ignore"
                                ],
                                "style" => "danger",
                                "url" => url("/tickets/ticket/".$ticket->id."?status=Archive")
                            ]
                        ]
                    ]
                ]),
            ]); 

            \Log::info('slack Messages sent.');;
        } catch (SlackErrorResponse $e) {
            \Log::info( 'Fail to send the message.'. $e->getMessage());; 
        }
    }
    public function slackTicketNotifSB($ticket) {
        $client = ClientFactory::create(env('SLACK_API_KEY_SB'));

        try {
            $subject = $ticket->ticket_subject;
            $subject = str_replace('Request for','',$subject);
            // This method requires your token to have the scope "chat:write"
            $time_diff = $this->getTimeDiff($ticket);
            $response = $client->chatPostMessage([ 
                'channel' => 'ticket-notif',
                'text' => 'Ticket '.$subject.' has been in Awaiting for Support Reply for '.$time_diff, 
                'blocks' => json_encode([
                    [
                        "type" => "section",
                        "text" => [
                            "type" => "plain_text",
                            "text" => 'Ticket '.$subject.' has been in Awaiting for Support Reply for '.$time_diff,
                            "emoji" => true
                        ]
                    ],
                    [
                        "type" => "actions",
                        "elements" => [
                            [
                                "type" => "button",
                                "text" => [
                                    "type" => "plain_text",
                                    "emoji" => true,
                                    "text" => "Visit"
                                ],
                                "url" => url("/tickets/ticket/".$ticket->id."")
                            ],
                            [
                                "type" => "button",
                                "text" => [
                                    "type" => "plain_text",
                                    "emoji" => true,
                                    "text" => "Mark as Resolved"
                                ],
                                "style" => "primary",
                                "url" => url("/tickets/ticket/".$ticket->id."?status=Closed")
                            ],
                            [
                                "type" => "button",
                                "text" => [
                                    "type" => "plain_text",
                                    "emoji" => true,
                                    "text" => "Ignore"
                                ],
                                "style" => "danger",
                                "url" => url("/tickets/ticket/".$ticket->id."?status=Archive")
                            ]
                        ]
                    ]
                ]),
            ]); 

            \Log::info('slack Messages sent.');;
        } catch (SlackErrorResponse $e) {
            \Log::info( 'Fail to send the message.'. $e->getMessage());; 
        }
    }
}
