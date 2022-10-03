<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrawlerPaysafeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:paysafe_accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawler for Paysafe Accounts';

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

        $accounts = $this->curlPost(env('APP_URL_HTTP').':4000/paysafe_crawler?type=paysafe_accounts');
        if($accounts != '') {
            $accounts = json_decode($accounts, true);
            \Log::info('accounts cron');
            \Log::info($accounts);
            
            // dd($accounts);
            try {
                foreach ($accounts as $key => $value) {
                    $account = new \App\PaysafeAccount();
                    $account = $account->where('merchant_number',$value['merchant_number'])->first();
                    if(!$account) {
                        $account = new \App\PaysafeAccount();
                    }

                    $account->app_number = $value['app_number'];
                    $account->merchant_number = $value['merchant_number'];
                    $account->merchant_name = $value['merchant_name'];
                    $account->agent_number = $value['agent_number'];
                    $account->agent_name = $value['agent_name'];
                    $account->rep_number = $value['rep_number'];
                    $account->rep_name = $value['rep_name'];
                    $account->status = $value['status'];
                    $account->approved_date = $value['approved_date'];
                    $account->closed_date = $value['closed_date'];
                    $account->corp_name = $value['corp_name'];
                    $account->phone_number = $value['phone_number'];
                    $account->owner_name = $value['owner_name'];
                    $account->crawled_batches = false;
                    $account->crawled_deposits = false;
                    $account->crawled_money_transactions = false;
                    $account->crawled_chargebacks = false;
                    $account->crawled_retrievals = false;
                    $account->save();
                }

                if(env('APP_ENV') == 'production') {
                    \DB::disconnect('promise_db');
                }

            } catch (\Throwable $th) {

                \Log::info($th);
                \Log::info('Crawler Error : error foreach');
                // return response()->json([
                //     'success' => false,
                //     'error' => json_encode($th)
                // ]);
            }
        }  else {
            \Log::info('Crawler Error : accounts cron');
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
