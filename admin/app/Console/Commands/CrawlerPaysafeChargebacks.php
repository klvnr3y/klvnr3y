<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrawlerPaysafeChargebacks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:paysafe_chargebacks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawler for Paysafe Chargebacks';

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

        $merchant = \App\PaysafeAccount::where('status','Open')->whereNotIn('merchant_number',['DELETED','DECLINED','PENDING'])->where('crawled_chargebacks',false)->first();
        if($merchant != '') {
            
            $chargebacks = $this->curlPost(env('APP_URL_HTTP').':4000/paysafe_crawler?type=paysafe_chargebacks&merchant_number='.$merchant->merchant_number);
            if($chargebacks != '') {
                $chargebacks = json_decode($chargebacks, true);
                \Log::info('chargeback list cron');
                // \Log::info($chargebacks);
                foreach ($chargebacks as $key => $value) {
                    $amount = str_replace(',','',str_replace('$','',$value['amount']));
                    $chargeback = new \App\PaysafeChargeback();
                    $chargeback = $chargeback->where('merchant_number',$value['merchant_number'])->where('case_number', $value['case_number'])->first();
                    if(!$chargeback) {
                        $chargeback = new \App\PaysafeChargeback();
                        $chargeback->merchant_number = $value['merchant_number'];
                        $chargeback->case_number = $value['case_number'];
                    } 
        
                    $chargeback->amount = $amount;
                    $chargeback->card_number = $value['card_number'];
                    $chargeback->transaction_date = $value['transaction_date'];
                    $chargeback->received_date = $value['received_date'];
                    $chargeback->resolved_date = $value['resolved_date'];
                    $chargeback->reason = $value['reason'];
                    $chargeback->save();
                }
                $merchant->crawled_chargebacks = true;
                $merchant->save();
                if(env('APP_ENV') == 'production') {
                    \DB::disconnect('promise_db');
                }
                

                echo json_encode($chargebacks);
            }  else {
                \Log::info('Crawler Error : chargeback list cron');
            }
            

        }
        
    }


    public function curlPost($url, $data=NULL, $headers = NULL) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        if(!empty($data)){
            curl_setopt($ch,CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
    
        $response = curl_exec($ch);
    
        if (curl_error($ch)) {
            trigger_error('Curl Error:' . curl_error($ch));
        }
    
        curl_close($ch);
        return $response;
    }
}
