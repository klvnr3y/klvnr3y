<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ddeboer\Imap\Server;
use Ddeboer\Imap\SearchExpression;
use App\User;
use App\Ticket;
use App\TicketResponse;
use Illuminate\Support\Facades\Mail;

class FetchEmailTicketResponse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'FetchEmailTicketResponse:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Job is to get ticket response from email inbox';

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
     * @return mixed
     */
    public function handle()
    {
        $this->getSupportEmails();
        $this->getNewTicketEmails();


        \Log::info("Cron is working fine!");
    }

    private function getSupportEmails() {
        // $server = new Server('promise.network');
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
        $connection = $server->authenticate('support@promise.network', 'Lc9aCJfxlaDf');
        $mailbox = $connection->getMailbox('INBOX');
        // $messages = $mailbox->getMessages();
        // dd($messages);
        $search = new SearchExpression();
        $search->addCondition(new \Ddeboer\Imap\Search\Flag\Unseen('UNSEEN'));
        $today = new \DateTimeImmutable();
        $daysAgo = $today->sub(new \DateInterval('P2D'));
        $search->addCondition(new \Ddeboer\Imap\Search\Date\Since($daysAgo));
        $messages = $mailbox->getMessages(
            $search
        );

        $received_response_emails = array();
        foreach ($messages as $key => $message) {
            $message->markAsSeen();
            $from = $message->getFrom()->getAddress();

            if(count($message->getTo()) > 0) {
                $to = $message->getTo()[0]->getAddress();

                $subject = $message->getSubject();
                $body = $message->getBodyHtml();
                $date = $message->getDate()->format('Y-m-d H:i:s');
                $date = date('Y-m-d H:i:s',strtotime($date.' '.$message->getDate()->getTimeZone()->getName()));
                $attachments = $message->getAttachments();

                array_push($received_response_emails,array(
                    'from' => $from,
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $body,
                    'date' => $date,
                    'attachments' => $attachments
                ));

            }
        }

        foreach ($received_response_emails as $key => $mail) {
            $user = User::where('email',$mail['from'])->get();
            if(!$user->isEmpty()) {
                $user = $user[0];
                $submitted_by = $user->id;
                $subject = $mail['subject'];
                if(!strpos($subject,'Ticket #')) {
                    $subject = str_replace('Re: TICKET REPLY: ','',$subject);
                    $ticket = Ticket::where('submitted_by',$submitted_by)
                                        ->where("ticket_subject",$subject)
                                        ->get();

                    if(!$ticket->isEmpty()) {
                        $ticket = $ticket[0];

                        $mail = $this->getBodyWithAttachmentUrl($mail,false);

                        $ticket_response = new TicketResponse();
                        $ticket_response = $ticket_response->where('response',$mail['body'])->where('ticket_id',$ticket->id)->get();
                        if($ticket_response->isEmpty()) {
                            $mail = $this->getBodyWithAttachmentUrl($mail,true);

                            $this->saveTicketResponse($user->id,$ticket->id,$mail['date'],$mail['body']);

                            $this->updateTicketStatus($ticket->id,'Awaiting Support Reply');

                            $submitted_by = $ticket->ticket_responses()->where('submitted_by','<>',$ticket->submitted_by)->latest('created_at')->first()->submitted_by()->first();

                            $to_name = $submitted_by->name;
                            $to_email = $submitted_by->email;
                            $subject = $mail['subject'].' Ticket #'.$ticket->id;
                            $data = array(
                                            'response' => $mail['body'],
                                        );
                            $this->sendEmail($to_name,$to_email,$subject,$data);
                        }
                    }
                } else {
                    $subject_ = explode('Ticket #',$subject);
                    $subject = str_replace('Re: TICKET REPLY: ','',$subject_[0]);
                    $ticket_id = $subject_[1];


                    $ticket = Ticket::find($ticket_id);
                    if(!is_null($ticket)) {
                        $mail = $this->getBodyWithAttachmentUrl($mail,false);

                        $ticket_response = TicketResponse::where('ticket_id',$ticket_id)
                                            ->where("response",$mail['body'])
                                            ->get();
                        if($ticket_response->isEmpty()) {
                            $this->updateTicketStatus($ticket_id,'Awaiting Customer Reply');

                            $mail = $this->getBodyWithAttachmentUrl($mail,true);

                            $this->saveTicketResponse($user->id,$ticket_id,$mail['date'],$mail['body']);

                            $ticket = Ticket::find($ticket_id);
                            $to_email = $ticket->submitted_by()->first()->email;
                            $to_name = $ticket->submitted_by()->first()->name;
                            $subject = $subject_[0];
                            $data = array(
                                'response' => $mail['body'],
                            );

                            $this->sendEmail($to_name,$to_email,$subject,$data);
                        }
                    }


                }

            }
        }
    }

    private function getNewTicketEmails() {
        // $server = new Server('promise.network');
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
        $connection = $server->authenticate('newticket@promise.network', 'q8P@lHJOjEvp');
        $mailbox = $connection->getMailbox('INBOX');
        // $messages = $mailbox->getMessages();
        // dd($messages);
        $search = new SearchExpression();
        $search->addCondition(new \Ddeboer\Imap\Search\Flag\Unseen('UNSEEN'));
        $today = new \DateTimeImmutable();
        $daysAgo = $today->sub(new \DateInterval('P2D'));
        $search->addCondition(new \Ddeboer\Imap\Search\Date\Since($daysAgo));
        $messages = $mailbox->getMessages(
            $search
        );

        $received_newticket_emails = array();
        foreach ($messages as $key => $message) {
            $message->markAsSeen();
            $from = $message->getFrom()->getAddress();

            if(count($message->getTo()) > 0) {
                $to = $message->getTo()[0]->getAddress();
                $subject = $message->getSubject();
                $body = $message->getBodyHtml();
                $date = $message->getDate()->format('Y-m-d H:i:s');
                $date = date('Y-m-d H:i:s',strtotime($date.' '.$message->getDate()->getTimeZone()->getName()));
                $attachments = $message->getAttachments();

                array_push($received_newticket_emails,array(
                    'from' => $from,
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $body,
                    'date' => $date,
                    'attachments' => $attachments
                ));
            }
        }


        foreach ($received_newticket_emails as $key => $mail) {
            $user = User::where('email',$mail['from'])->get()->first();
            if(is_null($user)) {
                // $user_id = $this->createNewUser($mail['from']);
                // $ticket = Ticket::where('submitted_by',env('APP_UNASSIGNED_ID'))
                //                         ->where("ticket_subject",'LIKE',"%".$mail['subject'].'%')
                //                         ->get()->first();
                $ticket = Ticket::where('email_date', $mail['date'])
                                        ->where("ticket_subject",'LIKE',"%".$mail['subject'].'%')
                                        ->get()->first();
                if(is_null($ticket)) {
                    $mail = $this->getBodyWithAttachmentUrl($mail,true);
                    $ticket_id = $this->createNewTicket(env('APP_UNASSIGNED_ID'),$mail);
                }

            } else {
                // $ticket = Ticket::where('submitted_by',$user->id)
                //                         ->where("ticket_subject",'LIKE',"%".$mail['subject'].'%')
                //                         ->get()->first();
                $ticket = Ticket::where('email_date', $mail['date'])
                                        ->where("ticket_subject",'LIKE',"%".$mail['subject'].'%')
                                        ->get()->first();
                if(is_null($ticket)) {
                    $mail = $this->getBodyWithAttachmentUrl($mail,true);
                    $ticket_id = $this->createNewTicket($user->id,$mail);
                }
            }
        }

    }

    private function createNewTicket($user_id,$mail) {
        $ticket = new Ticket();
        $ticket->submitted_by = $user_id;
        $ticket->ticket_subject = $mail['subject'];
        if($user_id == env('APP_UNASSIGNED_ID')) {
            $ticket->ticket_description = 'From: '.$mail['from'].'<br/><br/><br/>'.$mail['body'];
        } else {
            $ticket->ticket_description = $mail['body'];
        }
        $ticket->ticket_type = 'None';
        $ticket->ticket_priority = 'None';

        $ticket->email_date = $mail['date'];
        $ticket->save();

        $ticket->ticket_subject = $mail['subject'].' [## '.$ticket->id.' ##]';
        $ticket->save();
        return $ticket->id;
    }

    private function createNewUser($email) {
        $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');
        $password = substr($random, 0, 10);
        $user = User::create([
            'name' => $email,
            'email' => $email,
            'password' => bcrypt($password),
            'status' => 'Active',
            'role' => 'Merchant'
        ]);

        $to_name = $email;
        $to_email = $email;

        $data = array(
            'to_name' => $to_name,
            'to_email' => $to_email,
            'subject' => 'Welcome to Promise Network',
            'from_name' => env('MAIL_FROM_NAME'),
            'from_email' => env('MAIL_FROM_ADDRESS'),
            'template' => 'admin.emails.ticketnewuser',
            'body_data' => [
                'name' => $email,
                "email"=>$to_email,
                'password' => $password,
                'link'=> url('/')
            ]
        );

        event(new \App\Events\SendMailEvent($data));
        // Mail::send('admin.emails.ticketnewuser',$data, function($message) use ($data) {
        //     $message->to($to_email,$to_name)->subject('');;
        //     $message->from('support@promise.network','Promise Network');
        // });

        return $user->id;
    }

    private function getBodyWithAttachmentUrl($mail,$putcontent) {
        $file_urls = [];
        foreach ($mail['attachments'] as $key => $attachment) {
            // $name = $attachment->getStructure()->id;
            // $name = str_replace('<','',$name);
            // $name = str_replace('>','',$name);


            // $extension = strtolower($attachment->getStructure()->subtype);
            // $file_name = $name.'.'.$extension;

            $file_name = $attachment->getFilename();
            // \Log::info($file_name);

            $file_path = '/public/email/attachments/'.$file_name;
            if($putcontent) {
                // file_put_contents(
                //     $file_path,
                //     $attachment->getDecodedContent()
                // );

                \Storage::put($file_path, $attachment->getDecodedContent());
            }
            $file_path = str_replace('/public','/storage',$file_path);
            $file_urls[] = '<a target="_blank" href="'.url($file_path).'">'.$file_name.'</a>';
            // $_body = str_replace('cid:'.$file_name,url('/' .'uploads/'. $file_name),$_body);

            // $_body = '<div>'.$file_url.'</div>';

        }
        $file_urls = implode('<br/>',$file_urls);
        $file_urls = '<div>'.$file_urls.'</div>';
        // \Log::info("file urls");
        // \Log::info($file_urls);
        $mail['body'] = $file_urls.$mail['body'];
        return $mail;
    }

    private function saveTicketResponse($user_id,$ticket_id,$mail_date,$mail_body) {
        $ticket_response = new TicketResponse();
        $ticket_response->submitted_by = $user_id;
        $ticket_response->ticket_id = $ticket_id;
        $ticket_response->created_at = $mail_date;
        $ticket_response->response = $mail_body;
        $ticket_response->save();
    }

    private function updateTicketStatus($ticket_id,$ticket_status) {
        $ticket = Ticket::find($ticket_id);
        $ticket->ticket_status = $ticket_status;
        $ticket->status_change_updated_at = date('Y-m-d H:i:s');
        $ticket->save();
    }

    private function sendEmail($to_name,$to_email,$subject,$data) {

        $data = array(
            'to_name' => $to_name,
            'to_email' => $to_email,
            'subject' => $subject,
            'from_name' => env('MAIL_FROM_NAME').' Support',
            'from_email' => env('MAIL_FROM_ADDRESS'),
            'template' => 'admin.emails.ticketresponseadmin',
            'body_data' => $data
        );


        event(new \App\Events\SendMailEvent($data));
        // Mail::send('admin.emails.ticketresponseadmin',$data, function($message) use ($to_name, $to_email, $subject) {
        //     $message->to($to_email,$to_name)->subject($subject);;
        //     $message->from('support@promise.network','Promise Network Support');
        // });
    }


}
