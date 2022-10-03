<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrawlerPaysafeMoneyTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:paysafe_money_transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawler for Paysafe Money Transactions';

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

        $merchant = \App\PaysafeAccount::where('status','Open')->whereNotIn('merchant_number',['DELETED','DECLINED','PENDING'])->where('crawled_money_transactions',false)->first();
        if($merchant != '') {
            
            $money_transactions = $this->curlPost(env('APP_URL_HTTP').':4000/paysafe_crawler?type=paysafe_money_transactions&merchant_number='.$merchant->merchant_number);
            
            \Log::info('Money Trnsaction cron');
            \Log::info($money_transactions);
            if($money_transactions != '') {
                $money_transactions = json_decode($money_transactions, true);
                foreach ($money_transactions as $key => $value) {
                    $amount = str_replace(',','',str_replace('$','',$value['amount']));
                    $money_transaction = new \App\PaysafeMoneyTransaction();
                    $money_transaction = $money_transaction->where('transaction_date',$value['transaction_date'])->where('amount',$value['amount'])->where('merchant_number',$merchant->merchant_number)->first();
                    if(!$money_transaction) {
                        $money_transaction = new \App\PaysafeMoneyTransaction();
                        $money_transaction->transaction_date = $value['transaction_date'];
                        $money_transaction->amount = $amount;
                        $money_transaction->merchant_number = $merchant->merchant_number;
                    } 
        
                    $money_transaction->batch_number = $value['batch_number'];
                    $money_transaction->card_number = $value['card_number'];
                    $money_transaction->transaction_type = $value['transaction_type'];
                    $money_transaction->entry_mode = $value['entry_mode'];
                    $money_transaction->card_type = $value['card_type'];
                    $money_transaction->save();
                }

                $merchant->crawled_money_transactions = true;
                $merchant->save();
                if(env('APP_ENV') == 'production') {
                    \DB::disconnect('promise_db');
                }
                

                echo json_encode($money_transactions);
            } else {
                \Log::info('Crawler Error :Money Trnsaction cron');
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
