<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ddeboer\Imap\Server;

class FetchPurechatTranscript extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purechat:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'GET PURECHAT transcripts';

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
        $hostname = 'mail.promise.network';
        $port = '993';
        $flags = '/imap/ssl/novalidate-cert';
        
        $server = new Server(
            $hostname, // required
            $port,     // defaults to '993'
            $flags,    // defaults to '/imap/ssl/validate-cert'
            // $parameters
        );

            // $connection is instance of \Ddeboer\Imap\Connection
        $connection = $server->authenticate('transcriptsrelay@promise.network', 'bWMJV4cT8Xe9');
        $mailbox = $connection->getMailbox('INBOX');
        // $messages = $mailbox->getMessages();
        // dd($messages);
        $today = new \DateTimeImmutable();
        $daysAgo = $today->sub(new \DateInterval('P2D'));

        $messages = $mailbox->getMessages(
            new \Ddeboer\Imap\Search\Date\Since($daysAgo),
            \SORTDATE, // Sort criteria
            true // Descending order
        );

        $mails = array();
        foreach ($messages as $key => $message) {
            $from = $message->getFrom()->getAddress();

            if(count($message->getTo()) > 0) { 
                $to = $message->getTo()[0]->getAddress();

                $subject = $message->getSubject();
                $body = $message->getBodyHtml();
                $date = $message->getDate()->format('Y-m-d H:i:s');
                $date = date('mdY.Hi',strtotime($date.' '.$message->getDate()->getTimeZone()->getName()));
                $attachments = $message->getAttachments();

                
                $body_arr = explode(PHP_EOL,$body);
                $email_index = array_search("\t\t\tEmail:",$body_arr);
                if($email_index !== false) {
                    $email = $body_arr[$email_index +3];
                    $email = str_replace("\t",'',$email);
                    $merchant_name_index = array_search("\t\t\tMerchant Name:",$body_arr);
                    if($merchant_name_index !== false) {
                        $merchant_name = $body_arr[$merchant_name_index + 3];
                        $merchant_name =  str_replace("\t",'',$merchant_name);

                        $data = array(
                            'from' => $from,
                            'to' => $to,
                            // 'subject' => $subject,
                            'subject' => 'Chat Transcript - '. $merchant_name .' - '.$date,
                            'body' => $body,
                            'date' => $date,
                            'attachments' => $attachments
                        );

                        // dd($email);
                        $user = \App\User::where('email',$email)->first();
                        $ticket = \App\Ticket::where('ticket_subject',$data['subject'])->where('ticket_description',$data['body'])->first();
                        if(!$ticket) {
                            $ticket = \App\Ticket::create([
                                'ticket_subject' => $data['subject'],
                                'ticket_description' => $data['body'],
                                'submitted_by' => $user ? $user->id : 911,
                                'assigned_to' => 0,
                                'ticket_type' => 'None',
                                'ticket_priority' => 'None',
                                'ticket_status' => 'Awaiting Support Reply',
                                'status_change_updated_at' => date('Y-m-d H:i:s')
                            ]);



                            $data = array(
                                'full_name' => $user->name,
                                'email' => $user->email,
                                'send_by'=> $user->name,
                                'send_by_email'=>$user->email,
                                'phone_number' => $user->phone_number,
                                'address' => $user->address,
                                'link'=> url('/'),
                                'button_link'=> url('/')."/tickets/ticket"."/".$ticket->id
                            );


                            $template = 'admin.emails.purechat-notif';

                   
                            $mail_data = array(
                                'to_name' => $user->name,
                                'to_email' => $user->email,
                                'subject' => 'Response posted to your support ticket',
                                'from_name' => env('MAIL_FROM_NAME').' Support',
                                'from_email' => 'noreply@promise.network',
                                'template' => $template,
                                'body_data' => $data
                            );

                           
                            event(new \App\Events\SendMailEvent($mail_data));
                        }
                    }

                    
                }
                
            }
        }
    }
}
