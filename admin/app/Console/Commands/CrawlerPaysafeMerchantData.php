<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrawlerPaysafeMerchantData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:merchant_data {--split} {--index=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawler Merchant Data';

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
        $split = $this->option('split');
        $index = $this->option('index');
        $merchant_all = \App\PaysafeAccount::where('status','Open')->whereNotIn('merchant_number',['DELETED','DECLINED','PENDING'])->where('crawled_batches',false)->get();
        if(count($merchant_all) > 0) {
            if($split) {
                $count = round( $merchant_all->count() / 2);
                $merchant_chunked = array_chunk($merchant_all->toArray(), $count);
                $merchant_id = $merchant_chunked[$index][0]['id'];
                \Log::info('merchant_id');
                \Log::info($merchant_id);
    
                
                $merchant = \App\PaysafeAccount::find($merchant_id);
               
    
                if($merchant) {
                    
                    $merchant->crawled_batches = true;
                    $merchant->save();
    
                    \Log::info('merchant_number');
                    \Log::info($merchant->merchant_number);
                    $merchant_data = $this->curlPost(env('APP_URL_HTTP').':4000/paysafe_crawler?type=paysafe_merchant_data&merchant_number='.$merchant->merchant_number);
                    
                    if($merchant_data != 'error') {
                        $merchant_data = json_decode($merchant_data, true);
                        
                        \Log::info('merchant_data');
                        // \Log::info($merchant_data);
                        if($merchant_data) {

                            \App\PaysafeBatch::upsert(
                                    $merchant_data['batch_list'],
                                    ['batch_number','merchant_number'],
                                    ['closed_date','batch_date','net_items','batch_amount']
                                );
                            
                            \App\PaysafeBatchDetail::upsert(
                                $merchant_data['batch_details'],
                                ['trx_code'],
                                ['batch_number','merchant_number','trx_date','entry_mode','card_type','card_number','trx_type','trx_code','trx_amount']
                            );

                            \App\PaysafeDeposit::upsert(
                                $merchant_data['deposits'],
                                ['merchant_number','ach_date','amount'],
                                ['merchant_number','ach_date','transmission_date','trace_number','dda_number','tr_number','amount']
                            );
            
                            \App\PaysafeMoneyTransaction::upsert(
                                $merchant_data['money_transactions'],
                                ['transaction_date','amount','merchant_number'],
                                ['merchant_number','batch_number','card_number','transaction_date','amount','transaction_type','entry_mode','card_type']
                            );

                            \App\PaysafeChargeback::upsert(
                                $merchant_data['chargebacks'],
                                ['merchant_number','case_number'],
                                ['merchant_number','amount','case_number','card_number','transaction_date','received_date','resolved_date','reason']
                            );

                            \App\PaysafeRetrievals::upsert(
                                $merchant_data['retrievals'],
                                ['merchant_number','case_number'],
                                ['merchant_number','amount','family_id','case_number','card_number','transaction_date','received_date','resolved_date','reason']
                            );
        
                            \Log::info('Crawl Success');
                        } else {
                            \Log::info('Crawler Error : merchant data');
                            $merchant->crawled_batches = false;
                            $merchant->save();
                        }
                    } else {
                        \Log::info('Crawler Error : merchant data');
                        $merchant->crawled_batches = false;
                        $merchant->save();
                    }
                    
                    
                   
    
    
                } else {
                    \Log::info('Crawler Error : merchant data');
                    // $merchant->crawled_batches = false;
                    // $merchant->save();
                }
    
    
            }
        } else {
            \Log::info("all crawled");
        }
        

        if(env('APP_ENV') == 'production') {
            \DB::disconnect('promise_db');
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
