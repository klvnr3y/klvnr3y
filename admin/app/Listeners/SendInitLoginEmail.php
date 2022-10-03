<?php

namespace App\Listeners;

use App\Events\UserInitLoginUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendInitLoginEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserInitLoginUpdated  $event
     * @return void
     */
    public function handle(UserInitLoginUpdated $event)
    {
        $user = $event->user;
        $eula_pdf = $event->eula_pdf;

        $to_name = $user->name;
        $to_email = $user->email;
        $data = array(  'fullname' => $user->name,
                        "email"=>$to_email,
                        'datetime'=>date('Y-m-d h:i:s A'),
                        'link'=> url('')
                    );
        Mail::send('admin.emails.init-login',$data, function($message) use ($to_name, $to_email, $data) {
            $message->to($to_email,$to_name)->subject($to_name.' Terms accepted ('.env('MIX_APP_NAME').')');;

            $message->from('support@promise.network', env('MIX_APP_NAME'));

            // $body = view('admin.emails.init-login', $data)->render();
        });

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($eula_pdf);
        $content = $pdf->download()->getOriginalContent();
        $file_name = 'eula'.time().'.pdf';
        \Storage::put('public/'.$file_name,$content) ;

        $file_size = \Storage::size('public/'.$file_name);

        $data = \App\MerchantFiles::create([
            'user_id' => $user->id,
            'category' => 'EULA',
            'file_size' => $file_size,
            'file_name' => str_replace(' ','',$user->name). 'EULA.pdf',
            'file_url' => $file_name
        ]);

    }
}
