<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrawlerPaysafeBatchDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:paysafe_batch_details';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawler for Paysafe Batch Details';

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

        $batch = \App\PaysafeBatch::where('crawled',false)->orderBy('created_at','asc')->first();
        if($batch) {
            $url = env('APP_URL_HTTP').':4000/paysafe_crawler?type=paysafe_batch_details&merchant_number='.$batch->merchant_number.'&batch_no='.$batch->batch_number;
            
            $batch_details = $this->curlPost($url);
            if($batch_details != '') {
                $data = json_decode($batch_details, true);
                \Log::info('batch details cron');
                // \Log::info($data);
                
            
                foreach ($data as $key => $value) {
                    if($value['merchant_number'] != 'report total:') {
                        $batch_detail = new \App\PaysafeBatchDetail();
                        $batch_detail = $batch_detail->where('trx_code',$value['trx_code'])->first();
                        if(!$batch_detail) {
                            $batch_detail = new \App\PaysafeBatchDetail();

                            $batch_detail->trx_code = $value['trx_code'];
                        } 
                        $batch_detail->batch_number = $batch->batch_number;
                        $batch_detail->merchant_number = $value['merchant_number'];
                        $batch_detail->trx_date = $value['trx_date'];
                        $batch_detail->entry_mode = $value['entry_mode'];
                        $batch_detail->card_type = $value['card_type'];
                        $batch_detail->card_number = $value['card_number'];
                        $batch_detail->trx_type = $value['trx_type'];
                        $batch_detail->trx_amount = str_replace(',','',str_replace('$','',$value['trx_amount']));
                        $batch_detail->save();
                    }
                    
                }
                $batch->crawled = true;
                $batch->save();
                if(env('APP_ENV') == 'production') {
                    \DB::disconnect('promise_db');
                }
                
                echo $batch_details;
            } else {
                \Log::info('Crawler Error : batch details cron');
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
