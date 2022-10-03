<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrawlerPaysafeBatchList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:paysafe_batch_list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawler for Paysafe Batch List';

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

        $merchant = \App\PaysafeAccount::where('status','Open')->whereNotIn('merchant_number',['DELETED','DECLINED','PENDING'])->where('crawled_batches',false)->first();
        if($merchant) {
            
            $batches = $this->curlPost(env('APP_URL_HTTP').':4000/paysafe_crawler?type=paysafe_batches&merchant_number='.$merchant->merchant_number);
            if($batches != '') {
                $batches = json_decode($batches, true);
                \Log::info('batch list cron');
                // \Log::info($batches);
                foreach ($batches as $key => $value) {
                    $batch = new \App\PaysafeBatch();
                    $batch = $batch->where('batch_number',$value['batch_number'])->where('merchant_number',$value['merchant_number'])->first();
                    if(!$batch) {
                        $batch = new \App\PaysafeBatch();

                        $batch->merchant_number = $value['merchant_number'];
                        $batch->batch_number = $value['batch_number'];
                        $batch->crawled = false;
                    } 

                    $batch->batch_date = $value['batch_date'];
                    $batch->closed_date = date('Y-m-d',strtotime($value['closed_date']));
                    $batch->batch_date = date('Y-m-d',strtotime($value['batch_date']));
                    $batch->net_items = $value['net_items'];
                    $batch->batch_amount = str_replace(',','',str_replace('$','',$value['batch_amount']));
                    
                    $batch->save();
                }

                $merchant->crawled_batches = true;
                $merchant->save();
                if(env('APP_ENV') == 'production') {
                    \DB::disconnect('promise_db');
                }
                

                echo json_encode($batches);
            } else {
                \Log::info('Crawler Error : batch list cron');
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
