<?php

namespace App\Listeners;

use App\Events\NewGiftCardPurchaseEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NewGiftCardPurchaseListener
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
     * @param  NewGiftCardPurchaseEvent  $event
     * @return void
     */
    public function handle(NewGiftCardPurchaseEvent $event)
    {
        $merchant = $event->merchant;
        $params = $event->params;
        $invNumber = $event->invNumber;

        $to_name = $merchant->merchant_name;
        $to_email = $merchant->merchant_email;

        $customer_name = $params['billTo']['first_name'].' '.$params['billTo']['last_name'];
        $shipping_name = $params['shipTo']['first_name'].' '.$params['shipTo']['last_name'];
        $customer_email = $params['customer']['email'];
       

        $mail_data = array(  
            'to_name' => $to_name,
            'to_email' => $to_email,
            'subject' => 'Gift Card Purchase - '.$shipping_name.' '.$invNumber. ' '.$to_name,
            'from_name' => env('MAIL_FROM_NAME').' Support',
            'from_email' => env('MAIL_FROM_ADDRESS'),
            'template' => 'admin.emails.new-giftcard-purchase',
            // 'cc' => ['todd@posleader.com','cdebaca.jesse@gmail.com','joshuasaubon@gmail.com'],
            'cc' => ['todd@posleader.com','cdebaca.jesse@gmail.com'],
            'body_data' => [
                'fullname' => $to_name,
                "email"=>$to_email,
                'datetime'=>date('Y-m-d h:i:s A'),
                'shipTo' => $params['shipTo'],
                'billTo' => $params['billTo'],
                'amount' => $params['amount'],
                'invNumber' => $invNumber,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'special_message' => $params['message'],
                'link'=> url('/')
            ]
        );


        event(new \App\Events\SendMailEvent($mail_data));

        $mail_data = array(  
            'to_name' => $customer_name,
            'to_email' => $customer_email,
            'reply_to_name' => $to_name,
            'reply_to_email' => $to_email,
            'subject' => 'Gift Card Purchase - '.$shipping_name.' '.$invNumber. ' '.$to_name,
            'from_name' => env('MAIL_FROM_NAME').' Support',
            'from_email' => env('MAIL_FROM_ADDRESS'),
            'template' => 'admin.emails.new-giftcard-purchase',
            // 'cc' => ['joshuasaubon@gmail.com'],
            'cc' => [],
            'body_data' => [
                'fullname' => $to_name,
                "email"=>$to_email,
                'datetime'=>date('Y-m-d h:i:s A'),
                'shipTo' => $params['shipTo'],
                'billTo' => $params['billTo'],
                'amount' => $params['amount'],
                'invNumber' => $invNumber,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'special_message' => $params['message'],
                'link'=> url('/')
            ]
        );


        event(new \App\Events\SendMailEvent($mail_data));

        // Mail::send('admin.emails.new-giftcard-purchase',$data, function($message) use ($invNumber ,$to_name, $to_email, $customer_name, $shipping_name) {
        //     $message->to($to_email,$to_name)->subject();;
        //     $message->cc(['todd@posleader.com','cdebaca.jesse@gmail.com','joshuasaubon@gmail.com']);
        //     // $message->cc(['joshuasaubon@gmail.com']);
        //     $message->from('support@promise.network','Promise Network');
        // });

        // Mail::send('admin.emails.new-giftcard-purchase',$data, function($message) use ($invNumber ,$to_name, $to_email, $customer_name, $customer_email, $shipping_name) {
        //     $message->to($customer_email,$customer_name)->subject('Gift Card Purchase - '.$shipping_name.' '.$invNumber. ' '.$to_name);;
        //     $message->cc(['joshuasaubon@gmail.com']);
        //     $message->from('support@promise.network','Promise Network');
        // });
    }
}
