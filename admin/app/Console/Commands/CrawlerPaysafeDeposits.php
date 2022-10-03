<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrawlerPaysafeDeposits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:paysafe_deposits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawler for Paysafe Deposits';

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

        $merchant = \App\PaysafeAccount::where('status','Open')->whereNotIn('merchant_number',['DELETED','DECLINED','PENDING'])->where('crawled_deposits',false)->first();
        if($merchant != '') {
            
            $deposits = $this->curlPost(env('APP_URL_HTTP').':4000/paysafe_crawler?type=paysafe_deposits&merchant_number='.$merchant->merchant_number);
            if($deposits != '') {
                $deposits = json_decode($deposits, true);
                \Log::info('deposit list cron');
                // \Log::info($deposits);
                foreach ($deposits as $key => $value) {
                    $amount = str_replace(',','',str_replace('$','',$value['amount']));
                    $deposit = new \App\PaysafeDeposit();
                    $deposit = $deposit->where('merchant_number',$value['merchant_number'])->where('ach_date', $value['ach_date'])->where('amount',$amount)->first();
                    if(!$deposit) {
                        $deposit = new \App\PaysafeDeposit();
                        $deposit->crawled = false;
                        $deposit->merchant_number = $value['merchant_number'];
                        $deposit->ach_date = $value['ach_date'];
                        $deposit->amount = $amount;
                    } 
        
                    $deposit->transmission_date = $value['transmission_date'];
                    $deposit->trace_number = $value['trace_number'];
                    $deposit->dda_number = $value['dda_number'];
                    $deposit->tr_number = $value['tr_number'];
                    $deposit->save();
                }
                $merchant->crawled_deposits = true;
                $merchant->save();
                if(env('APP_ENV') == 'production') {
                    \DB::disconnect('promise_db');
                }
                

                echo json_encode($deposits);
            }  else {
                \Log::info('Crawler Error : deposit list cron');
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
