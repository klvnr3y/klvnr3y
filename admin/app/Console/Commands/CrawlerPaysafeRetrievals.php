<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrawlerPaysafeRetrievals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:paysafe_retrievals';

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

        $merchant = \App\PaysafeAccount::where('status','Open')->whereNotIn('merchant_number',['DELETED','DECLINED','PENDING'])->where('crawled_retrievals',false)->first();
        if($merchant != '') {
            
            $retrievals = $this->curlPost(env('APP_URL_HTTP').':4000/paysafe_crawler?type=paysafe_retrievals&merchant_number='.$merchant->merchant_number);
            if($retrievals != '') {
                $retrievals = json_decode($retrievals, true);
                \Log::info('retrieval list cron');

                foreach ($retrievals as $key => $value) {
                    $amount = str_replace(',','',str_replace('$','',$value['amount']));
                    $retrieval = new \App\PaysafeRetrievals();
                    $retrieval = $retrieval->where('merchant_number',$value['merchant_number'])->where('case_number', $value['case_number'])->first();
                    if(!$retrieval) {
                        $retrieval = new \App\PaysafeRetrievals();
                        $retrieval->merchant_number = $value['merchant_number'];
                        $retrieval->case_number = $value['case_number'];
                    }
                    
                    $retrieval->amount = $amount;
                    $retrieval->family_id = $value['family_id'];
                    $retrieval->card_number = $value['card_number'];
                    $retrieval->transaction_date = $value['transaction_date'];
                    $retrieval->received_date = $value['received_date'];
                    $retrieval->resolved_date = $value['resolved_date'];
                    $retrieval->reason = $value['reason'];
                    $retrieval->save();
                    
                }
                $merchant->crawled_retrievals = true;
                $merchant->save();
                if(env('APP_ENV') == 'production') {
                    \DB::disconnect('promise_db');
                }
                

                echo json_encode($retrievals);
            }  else {
                \Log::info('Crawler Error : retrieval list cron');
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
