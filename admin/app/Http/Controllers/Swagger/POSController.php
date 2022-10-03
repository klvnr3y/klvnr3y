<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class POSController extends Controller
{
    /**
     * @OA\Post(
     *     path="/POS/Checkbalance",
     *     summary="Checks the balance of the card specified",
     *     security={* {"passport": {}}, *},
        * @OA\RequestBody(
        *    required=true,
        *    description="The request.",
        *    @OA\JsonContent(
        *       required={"HardwareID","CardNumber","Amount","TipAmount","TransactionRef","CashierRef","ReferenceNumber","Force"},
        *       @OA\Property(property="HardwareID", type="string", example="TestHardWare1"),
        *       @OA\Property(property="CardNumber", type="string", example="TestCardNumber1"),
        *       @OA\Property(property="Amount", type="integer", example=0),
        *       @OA\Property(property="TipAmount", type="integer", example=0),
        *       @OA\Property(property="TransactionRef", type="string", example="TransactionRef4k109"),
        *       @OA\Property(property="CashierRef", type="string", example="CashierRef106"),
        *       @OA\Property(property="ReferenceNumber", type="string", example="ReferenceNumber105"),
        *       @OA\Property(property="Force", type="boolean", example=false),
        *    ),
        * ),
        *

     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *            @OA\JsonContent(
     *                  @OA\Property(property="Message", type="string"),
     *                  @OA\Property(property="ReferenceCode",type="string"),
     *                  @OA\Property(property="FinalTransaction",type="integer",example=0),
     *                  @OA\Property(property="CardBalance",type="integer",example=0),
     *              ),
     *     ),
     *     @OA\Response(
     *          response="401", description="Authorization has been denied for this request"
     *     ),
     *     @OA\Response(
     *          response="400", description="Business logic failure. Must process response body to determine details of failure reason.",
     *          @OA\JsonContent(
     *                 @OA\Property(property="ErrorCode",type="integer",example=0),
     *                 @OA\Property(property="Message",type="string"),
     *          )
     *     ),
     * )
     *
     */
    public function checkBalance(Request $request)
    {


        \Log::info(json_encode($request->fullUrl()));
        \Log::info(json_encode(getallheaders()));
        \Log::info(json_encode($request->all()));

        $headers = getallheaders();
        $ip_address = isset($headers['X-Real-IP']) ? $headers['X-Real-IP'] : '';
        $terminal = \App\MerchantGiftCardAccountTerminal::where('hardware_id', $request->HardwareID)->first();
        $accountCard = \App\MerchantGiftCardAccountCard::where('card_number',$request->CardNumber)->orderBy('id','desc')->first();
        if($accountCard) {
            if($accountCard->isActive == 0) {
                \Log::info('isActive update to 1');
                $accountCard->isActive = 1;
                $accountCard->save();
            }

            $data = [
                "Message" => null,
                "ReferenceCode" => $this->generateRandomString(8),
                "FinalTransactionAmount" => 0,
                "CardBalance" => $accountCard->balance
            ];


            // if(!$terminal) {
            //     return response()->json([
            //         'ErrorCode' => 400,
            //         'Message' => 'Terminal Does not exist'
            //     ]);
            // }

            $transaction = \App\MerchantGiftCardAccountCardTransaction::updateOrCreate(
                [
                    'account_id' => $accountCard->account_id,
                    'card_id' => $accountCard->id,
                    'terminal_id' => $terminal ? $terminal->id : null,
                    'card_number' => $request->CardNumber,
                    'cashier' => $request->CashierRef,
                    'amount' => $request->Amount,
                    'transaction_ref' => $request->TransactionRef,
                    'transaction_type' => 'BalanceCheck',
                    'source_ip' => $ip_address
                ],
                [
                'account_id' => $accountCard->account_id,
                'card_id' => $accountCard->id,
                'terminal_id' => $terminal ? $terminal->id : null,
                'guid' => '',
                'card_number' => $request->CardNumber,
                'reference_code' => $data['ReferenceCode'],
                'transaction_type' => 'BalanceCheck',
                'cashier' => $request->CashierRef,
                'amount' => $data['CardBalance'],
                'processed_date' => date('Y-m-d H:i:s'),
                'voided_date' => '',
                'transaction_ref' => $request->TransactionRef,
                'tip_amount' => $request->TipAmount,
                'final_transaction_amount' => '',
                'affects_card_value' => false,
                    'source_ip' => $ip_address
            ]);

            if($transaction) {
                \Log::info('checkbalance response');
                \Log::info($data);
                \App\MerchantGiftCardLog::create([
                    'request_header' => json_encode($headers),
                    'request_body' => json_encode($request->all()),
                    'response_body' => json_encode($data),
                    'transaction_type' => 'BalanceCheck',
                    'account_id' => $terminal->account_id,
                ]);
                return response()->json($data);
            } else {
                \Log::info([
                    'ErrorCode' => 400,
                    'Message' => 'Something is wrong, contact administrator'
                ]);
                \App\MerchantGiftCardLog::create([
                    'request_header' => json_encode($headers),
                    'request_body' => json_encode($request->all()),
                    'response_body' => json_encode([
                        'ErrorCode' => 400,
                        'Message' => 'Something is wrong, contact administrator'
                    ]),
                    'transaction_type' => 'BalanceCheck',
                    'account_id' => $terminal->account_id,
                ]);
                return response()->json([
                    'ErrorCode' => 400,
                    'Message' => 'Something is wrong, contact administrator'
                ]);
            }
        } else {


            $transaction = \App\MerchantGiftCardAccountCardTransaction::create([
                'account_id' => $terminal ? $terminal->account_id : null,
                'card_id' => null,
                'terminal_id' => $terminal ? $terminal->id : null,
                'guid' => '',
                'card_number' => $request->CardNumber,
                'reference_code' => '',
                'transaction_type' => 'BalanceCheck',
                'cashier' => $request->CashierRef,
                'amount' => 0,
                'processed_date' => date('Y-m-d H:i:s'),
                'voided_date' => '',
                'transaction_ref' => $request->TransactionRef,
                'tip_amount' => $request->TipAmount,
                'final_transaction_amount' => '',
                'affects_card_value' => false,
                'source_ip' => $ip_address
            ]);

            \Log::info([
                'ErrorCode' => 400,
                'Message' => 'Card Not Found'
            ]);
            \App\MerchantGiftCardLog::create([
                'request_header' => json_encode($headers),
                'request_body' => json_encode($request->all()),
                'response_body' => json_encode([
                    'ErrorCode' => 400,
                    'Message' => 'Card Not Found'
                ]),
                'transaction_type' => 'BalanceCheck',
                'account_id' => $terminal->account_id,
            ]);
            return response()->json([
                'ErrorCode' => 400,
                'Message' => 'Card Not Found'
            ]);
        }
    }
    /**
     * @OA\Post(
     *     path="/POS/Activate",
     *     summary="Activates the specific card",
     *     security={* {"passport": {}}, *},
        * @OA\RequestBody(
        *    required=true,
        *    description="The request.",
        *    @OA\JsonContent(
        *       required={"HardwareID","CardNumber","Amount","TipAmount","TransactionRef","CashierRef","ReferenceNumber","Force"},
        *       @OA\Property(property="HardwareID", type="string", example="TestHardWare1"),
        *       @OA\Property(property="CardNumber", type="string", example="TestCardNumber1"),
        *       @OA\Property(property="Amount", type="integer", example=0),
        *       @OA\Property(property="TipAmount", type="integer", example=0),
        *       @OA\Property(property="TransactionRef", type="string", example="TransactionRef4k109"),
        *       @OA\Property(property="CashierRef", type="string", example="CashierRef106"),
        *       @OA\Property(property="ReferenceNumber", type="string", example="ReferenceNumber105"),
        *       @OA\Property(property="Force", type="boolean", example=false),
        *    ),
        * ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *            @OA\JsonContent(
     *                  @OA\Property(property="Message", type="string"),
     *                  @OA\Property(property="ReferenceCode",type="string"),
     *                  @OA\Property(property="FinalTransaction",type="integer",example=0),
     *                  @OA\Property(property="CardBalance",type="integer",example=0),
     *              ),
     *     ),
     *     @OA\Response(
     *          response="401", description="Authorization has been denied for this request"
     *     ),
     *     @OA\Response(
     *          response="400", description="Business logic failure. Must process response body to determine details of failure reason.",
     *          @OA\JsonContent(
     *                 @OA\Property(property="ErrorCode",type="integer",example=0),
     *                 @OA\Property(property="Message",type="string"),
     *          )
     *     ),
     * )
     *
     */
    public function activate(Request $request)
    {


        \Log::info(json_encode($request->fullUrl()));
        \Log::info(json_encode(getallheaders()));
        \Log::info(json_encode($request->all()));

        $headers = getallheaders();
        $ip_address = isset($headers['X-Real-IP']) ? $headers['X-Real-IP'] : '';

        // {
        //     "HardwareID": "TestHardWare1",
        //     "CardNumber": "TestCardNumber1",
        //     "Amount": 0,
        //     "TipAmount": 0,
        //     "TransactionRef": "TransactionRef4k109",
        //     "CashierRef": "CashierRef106",
        //     "ReferenceNumber": "ReferenceNumber105",
        //     "Force": false
        //   }
        $terminal = \App\MerchantGiftCardAccountTerminal::where('hardware_id',$request->HardwareID)->with('gift_card_account')->get()->first();
        // if(!$terminal) {
        //     return response()->json([
        //         'ErrorCode' => 400,
        //         'Message' => 'Terminal Does not exist'
        //     ]);
        // }
        // $account = $terminal->gift_card_account;
        $card = \App\MerchantGiftCardAccountCard::where('card_number',$request->CardNumber)->get()->first();
        if($card) {
            if($card->isActive == 1) {
                $data = [
                    "Message" => null,
                    "ReferenceCode" => $this->generateRandomString(8),
                    "FinalTransactionAmount" => (float)$request->Amount + (float)$request->TipAmount,
                    "CardBalance" => $card->balance
                ];

                $transaction = \App\MerchantGiftCardAccountCardTransaction::create([
                    'account_id' => $card->account_id,
                    'card_id' => $card->id,
                    'terminal_id' => $terminal ? $terminal->id : null,
                    'guid' => '',
                    'card_number' => $request->CardNumber,
                    'reference_code' => $data['ReferenceCode'],
                    'transaction_type' => 'Activate',
                    'cashier' => $request->CashierRef,
                    'amount' => $data['CardBalance'],
                    'processed_date' => date('Y-m-d H:i:s'),
                    'voided_date' => '',
                    'transaction_ref' => $request->TransactionRef,
                    'tip_amount' => $request->TipAmount,
                    'final_transaction_amount' => (float)$request->Amount + (float)$request->TipAmount,
                    'affects_card_value' => true,
                    'source_ip' => $ip_address,
                ]);
                \Log::info([
                    'ErrorCode' => 607,
                    'Message' => 'Card already activated'
                ]);
                \App\MerchantGiftCardLog::create([
                    'request_header' => json_encode($headers),
                    'request_body' => json_encode($request->all()),
                    'response_body' => json_encode([
                        'ErrorCode' => 607,
                        'Message' => 'Card already activated'
                    ]),
                    'transaction_type' => 'Activate',
                    'account_id' => $terminal->account_id,
                ]);
                return response()->json([
                    'ErrorCode' => 607,
                    'Message' => 'Card already activated'
                ]);
            } else {
                $data = [
                    "Message" => null,
                    "ReferenceCode" => $this->generateRandomString(8),
                    "FinalTransactionAmount" => (float)$request->Amount + (float)$request->TipAmount,
                    "CardBalance" => (float)$request->Amount + (float)$request->TipAmount
                ];

                $card->balance = (float)$request->Amount + (float)$request->TipAmount;
                $card->isActive = 1;
                $card->save();
                $transaction = \App\MerchantGiftCardAccountCardTransaction::create([
                    'account_id' => $card->account_id,
                    'card_id' => $card->id,
                    'terminal_id' => $terminal ? $terminal->id : null,
                    'guid' => '',
                    'card_number' => $request->CardNumber,
                    'reference_code' => $data['ReferenceCode'],
                    'transaction_type' => 'Activate',
                    'cashier' => $request->CashierRef,
                    'amount' => $data['CardBalance'],
                    'processed_date' => date('Y-m-d H:i:s'),
                    'voided_date' => '',
                    'transaction_ref' => $request->TransactionRef,
                    'tip_amount' => $request->TipAmount,
                    'final_transaction_amount' => (float)$request->Amount + (float)$request->TipAmount,
                    'affects_card_value' => true,
                    'source_ip' => $ip_address,
                ]);
            }
        } else {

            $card = \App\MerchantGiftCardAccountCard::create([
                'account_id' => $terminal ? $terminal->account_id : null,
                'isActive' => true,
                'guid' => '',
                'card_number' => $request->CardNumber,
                'balance' => $request->Amount,
                'isPromotional' => false,
                'activation_date' => date('Y-m-d H:i:s'),
                'expiration_date' => ''
            ]);

            if(!$card) {
                \Log::info([
                    'ErrorCode' => 400,
                    'Message' => 'Card Could not be created'
                ]);
                \App\MerchantGiftCardLog::create([
                    'request_header' => json_encode($headers),
                    'request_body' => json_encode($request->all()),
                    'response_body' => json_encode([
                        'ErrorCode' => 400,
                        'Message' => 'Card Could not be created'
                    ]),
                    'transaction_type' => 'Activate',
                    'account_id' => $terminal->account_id,
                ]);
                return response()->json([
                    'ErrorCode' => 400,
                    'Message' => 'Card Could not be created'
                ],400);
            }

            $data = [
                "Message" => null,
                "ReferenceCode" => $this->generateRandomString(8),
                "FinalTransactionAmount" => (float)$request->Amount + (float)$request->TipAmount,
                "CardBalance" => $card->balance
            ];

            $transaction = \App\MerchantGiftCardAccountCardTransaction::create([
                'account_id' => $terminal ? $terminal->account_id : null,
                'card_id' => $card->id,
                'terminal_id' => $terminal ? $terminal->id : null,
                'guid' => '',
                'card_number' => $request->CardNumber,
                'reference_code' => $data['ReferenceCode'],
                'transaction_type' => 'Activate',
                'cashier' => $request->CashierRef,
                'amount' => $data['CardBalance'],
                'processed_date' => date('Y-m-d H:i:s'),
                'voided_date' => '',
                'transaction_ref' => $request->TransactionRef,
                'tip_amount' => $request->TipAmount,
                'final_transaction_amount' => (float)$request->Amount + (float)$request->TipAmount,
                'affects_card_value' => true,
                    'source_ip' => $ip_address,
            ]);
        }


        if(!$transaction) {
            \Log::info([
                'ErrorCode' => 400,
                'Message' => 'Transaction Failed'
            ]);
            \App\MerchantGiftCardLog::create(
                [
                    'request_header' => json_encode($headers),
                    'request_body' => json_encode($request->all()),
                    'response_body' => json_encode([
                        'ErrorCode' => 400,
                        'Message' => 'Transaction Failed'
                    ]),
                    'transaction_type' => 'Activate',
                    'account_id' => $terminal->account_id,
                ]
            );
            return response()->json([
                'ErrorCode' => 400,
                'Message' => 'Transaction Failed'
            ],400);
        }

        \App\MerchantGiftCardLog::create(
            [
                'request_header' => json_encode($headers),
                'request_body' => json_encode($request->all()),
                'response_body' => json_encode($data),
                'transaction_type' => 'Activate',
                'account_id' => $terminal->account_id,
            ]
        );
        return response()->json($data);


    }
    /**
     * @OA\Post(
     *     path="/POS/AddValue",
     *     summary="Adds the given value to the balance of a given card",
     *     security={* {"passport": {}}, *},
        * @OA\RequestBody(
        *    required=true,
        *    @OA\JsonContent(
        *    description="The request.",
        *       required={"HardwareID","CardNumber","Amount","TipAmount","TransactionRef","CashierRef","ReferenceNumber","Force"},
        *       @OA\Property(property="HardwareID", type="string", example="TestHardWare1"),
        *       @OA\Property(property="CardNumber", type="string", example="TestCardNumber1"),
        *       @OA\Property(property="Amount", type="integer", example=0),
        *       @OA\Property(property="TipAmount", type="integer", example=0),
        *       @OA\Property(property="TransactionRef", type="string", example="TransactionRef4k109"),
        *       @OA\Property(property="CashierRef", type="string", example="CashierRef106"),
        *       @OA\Property(property="ReferenceNumber", type="string", example="ReferenceNumber105"),
        *       @OA\Property(property="Force", type="boolean", example=false),
        *    ),
        * ),
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *            @OA\JsonContent(
     *                  @OA\Property(property="Message", type="string"),
     *                  @OA\Property(property="ReferenceCode",type="string"),
     *                  @OA\Property(property="FinalTransaction",type="integer",example=0),
     *                  @OA\Property(property="CardBalance",type="integer",example=0),
     *              ),
     *     ),
     *     @OA\Response(
     *          response="401", description="Authorization has been denied for this request"
     *     ),
     *     @OA\Response(
     *          response="400", description="Business logic failure. Must process response body to determine details of failure reason.",
     *          @OA\JsonContent(
     *                 @OA\Property(property="ErrorCode",type="integer",example=0),
     *                 @OA\Property(property="Message",type="string"),
     *          )
     *     ),
     * )
     *
     */
    public function addValue(Request $request)
    {


        \Log::info(json_encode($request->fullUrl()));
        \Log::info(json_encode(getallheaders()));
        \Log::info(json_encode($request->all()));

        $headers = getallheaders();
        $ip_address = isset($headers['X-Real-IP']) ? $headers['X-Real-IP'] : '';

        $terminal = \App\MerchantGiftCardAccountTerminal::where('hardware_id',$request->HardwareID)->with('gift_card_account')->get()->first();
        // if(!$terminal) {
        //     return response()->json([
        //         'ErrorCode' => 400,
        //         'Message' => 'Terminal Does not exist'
        //     ]);
        // }
        $account = $terminal->gift_card_account;

        $card = \App\MerchantGiftCardAccountCard::where('card_number',$request->CardNumber)->orderBy('id','desc')->first();
        if($card) {

            $existing = \App\MerchantGiftCardAccountCardTransaction::where('card_number',$request->CardNumber)
                ->where('transaction_ref',$request->TransactionRef)
                ->where('amount',$request->Amount)
                ->where('transaction_type','AddValue')
                ->get()
                ->first();
            if(!$existing) {
                if($card->isActive == 0) {
                    \Log::info('isActive update to 1');
                    $card->isActive = 1;
                }
                $card->balance = (float)$card->balance + ((float)$request->Amount + (float)$request->TipAmount);
                if($card->balance <= $account->max_activation_value) {
                    $card->save();
                } else {
                    \App\MerchantGiftCardLog::create([
                        'request_header' => json_encode($headers),
                        'request_body' => json_encode($request->all()),
                        'response_body' => json_encode([
                            'ErrorCode' => 500,
                            "Message" => "We were unable to process your request"
                        ]),
                        'transaction_type' => 'AddValue',
                        'account_id' => $terminal->account_id,
                    ]);
                    return response()->json([
                        'ErrorCode' => 500,
                        "Message" => "We were unable to process your request"
                    ],500);
                }


            }
            $data = [
                "Message" => null,
                "ReferenceCode" => $this->generateRandomString(8),
                "FinalTransactionAmount" => (float)$request->Amount + (float)$request->TipAmount,
                "CardBalance" => $card->balance
            ];

            $transaction = \App\MerchantGiftCardAccountCardTransaction::updateOrCreate(
                [
                    'id' => $existing ? $existing->id : null
                ],
                [
                    'account_id' => $card->account_id,
                    'card_id' => $card->id,
                    'terminal_id' => $terminal ? $terminal->id : null,
                    'guid' => '',
                    'card_number' => $request->CardNumber,
                    'reference_code' => $data['ReferenceCode'],
                    'transaction_type' => 'AddValue',
                    'cashier' => $request->CashierRef,
                    'amount' => $request->Amount,
                    'processed_date' => date('Y-m-d H:i:s'),
                    'voided_date' => '',
                    'transaction_ref' => $request->TransactionRef,
                    'tip_amount' => $request->TipAmount,
                    'final_transaction_amount' => (float)$request->Amount + (float)$request->TipAmount,
                    'affects_card_value' => true,
                    'source_ip' => $ip_address,
                ]
            );
        } else {
            $data = [
                "Message" => null,
                "ReferenceCode" => $this->generateRandomString(8),
                "FinalTransactionAmount" => (float)$request->Amount + (float)$request->TipAmount,
                "CardBalance" => 0
            ];
            $transaction = \App\MerchantGiftCardAccountCardTransaction::create(
                [
                    'account_id' => $terminal ? $terminal->account_id : null,
                    'card_id' => null,
                    'terminal_id' => $terminal ? $terminal->id : null,
                    'guid' => '',
                    'card_number' => $request->CardNumber,
                    'reference_code' => $data['ReferenceCode'],
                    'transaction_type' => 'AddValue',
                    'cashier' => $request->CashierRef,
                    'amount' => $request->Amount,
                    'processed_date' => date('Y-m-d H:i:s'),
                    'voided_date' => '',
                    'transaction_ref' => $request->TransactionRef,
                    'tip_amount' => null,
                    'final_transaction_amount' => null,
                    'affects_card_value' => false,
                    'source_ip' => $ip_address
                ]
            );

            \Log::info([
                'ErrorCode' => 850,
                // 'Message' => 'The card specified is not active'
                'Message' => 'card not found'
            ]);
            \App\MerchantGiftCardLog::create([
                'request_header' => json_encode($headers),
                'request_body' => json_encode($request->all()),
                'response_body' => json_encode([
                    'ErrorCode' => 850,
                    'Message' => 'card not found'
                ]),
                'transaction_type' => 'AddValue',
                'account_id' => $terminal->account_id,
            ]);
            return response()->json([
                'ErrorCode' => 850,
                'Message' => 'card not found'
            ],400);
        }



        if(!$transaction) {
            \Log::info([
                'ErrorCode' => 400,
                'Message' => 'Transaction Failed'
            ]);
            \App\MerchantGiftCardLog::create(
                [
                    'request_header' => json_encode($headers),
                    'request_body' => json_encode($request->all()),
                    'response_body' => json_encode([
                        'ErrorCode' => 400,
                        'Message' => 'Transaction Failed'
                    ]),
                    'transaction_type' => 'AddValue',
                    'account_id' => $terminal->account_id,
                ]
            );
            return response()->json([
                'ErrorCode' => 400,
                'Message' => 'Transaction Failed'
            ],400);
        }


        \Log::info('add Value');
        \Log::info(response()->json($data, 200,
        ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE));
        \App\MerchantGiftCardLog::create(
            [
                'request_header' => json_encode($headers),
                'request_body' => json_encode($request->all()),
                'response_body' => json_encode($data),
                'transaction_type' => 'AddValue',
                'account_id' => $terminal->account_id,
            ]
        );
        return response()->json($data, 200,
        ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }
    /**
         * @OA\Post(
         *     path="/POS/Redeem",
         *     summary="Redeems the given amount from a specific card",
         *     security={* {"passport": {}}, *},
            * @OA\RequestBody(
            *    required=true,
            *    description="The request.",
            *    @OA\JsonContent(
            *       required={"HardwareID","CardNumber","Amount","TipAmount","TransactionRef","CashierRef","ReferenceNumber","Force"},
            *       @OA\Property(property="HardwareID", type="string", example="TestHardWare1"),
            *       @OA\Property(property="CardNumber", type="string", example="TestCardNumber1"),
            *       @OA\Property(property="Amount", type="integer", example=0),
            *       @OA\Property(property="TipAmount", type="integer", example=0),
            *       @OA\Property(property="TransactionRef", type="string", example="TransactionRef4k109"),
            *       @OA\Property(property="CashierRef", type="string", example="CashierRef106"),
            *       @OA\Property(property="ReferenceNumber", type="string", example="ReferenceNumber105"),
            *       @OA\Property(property="Force", type="boolean", example=false),
            *    ),
            * ),
        *     @OA\Response(
        *          response="200",
        *          description="Success",
        *            @OA\JsonContent(
        *                  @OA\Property(property="Message", type="string"),
        *                  @OA\Property(property="ReferenceCode",type="string"),
        *                  @OA\Property(property="FinalTransaction",type="integer",example=0),
        *                  @OA\Property(property="CardBalance",type="integer",example=0),
        *              ),
        *     ),
        *     @OA\Response(
        *          response="401", description="Authorization has been denied for this request"
        *     ),
        *     @OA\Response(
        *          response="400", description="Business logic failure. Must process response body to determine details of failure reason.",
        *          @OA\JsonContent(
        *                 @OA\Property(property="ErrorCode",type="integer",example=0),
        *                 @OA\Property(property="Message",type="string"),
        *          )
        *     ),
        * )
        *
     */
    public function redeem(Request $request)
    {


        \Log::info(json_encode($request->fullUrl()));
        \Log::info(json_encode(getallheaders()));
        \Log::info(json_encode($request->all()));

        $headers = getallheaders();
        $ip_address = isset($headers['X-Real-IP']) ? $headers['X-Real-IP'] : '';

        $terminal = \App\MerchantGiftCardAccountTerminal::where('hardware_id',$request->HardwareID)->with('gift_card_account')->get()->first();
        // if(!$terminal) {
        //     return response()->json([
        //         'ErrorCode' => 400,
        //         'Message' => 'Terminal Does not exist'
        //     ]);
        // }
        // $account = $terminal->gift_card_account;

        $card = \App\MerchantGiftCardAccountCard::where('card_number',$request->CardNumber)->where('isActive',1)->orderBy('id','desc')->first();
        if($card) {
            $card->balance = (float)$card->balance - ((float)$request->Amount + (float)$request->TipAmount);
            $data = [
                "Message" => null,
                "ReferenceCode" => $this->generateRandomString(8),
                "FinalTransactionAmount" => (float)$request->Amount + (float)$request->TipAmount,
                "CardBalance" => $card->balance
            ];

            if( $card->balance < 0 ){
                $transaction = \App\MerchantGiftCardAccountCardTransaction::create(
                    [
                        'account_id' => $card->account_id,
                        'card_id' => null,
                        'terminal_id' => $terminal ? $terminal->id : null,
                        'guid' => '',
                        'card_number' => $request->CardNumber,
                        'reference_code' => $data['ReferenceCode'],
                        'transaction_type' => 'Redeem',
                        'cashier' => $request->CashierRef,
                        'amount' => $request->Amount,
                        'processed_date' => date('Y-m-d H:i:s'),
                        'voided_date' => '',
                        'transaction_ref' => $request->TransactionRef,
                        'tip_amount' => null,
                        'final_transaction_amount' => null,
                        'affects_card_value' => false,
                    'source_ip' => $ip_address
                    ]
                );

                \Log::info([
                    'ErrorCode' => 700,
                    'Message' => 'Card has insufficient funds'
                ]);
                \App\MerchantGiftCardLog::create([
                    'request_header' => json_encode($headers),
                    'request_body' => json_encode($request->all()),
                    'response_body' => json_encode([
                        'ErrorCode' => 700,
                        'Message' => 'Card has insufficient funds'
                    ]),
                    'transaction_type' => 'Redeem',
                    'account_id' => $terminal->account_id,
                ]);
                return response()->json([
                    'ErrorCode' => 700,
                    'Message' => 'Card has insufficient funds'
                ],400);
            }


            $existing = \App\MerchantGiftCardAccountCardTransaction::where('card_number',$request->CardNumber)
                ->where('transaction_ref',$request->TransactionRef)
                ->where('amount',$request->Amount)
                ->where('transaction_type','Redeem')
                ->get()
                ->first();
            if(!$existing) {
                if($card->balance >= 0){
                    $card->save();
                }
            }


            $transaction = \App\MerchantGiftCardAccountCardTransaction::updateOrCreate(
                [
                    'id' => $existing ? $existing->id : null
                ],
                [
                    'account_id' => $card->account_id,
                    'card_id' => $card->id,
                    'terminal_id' => $terminal ? $terminal->id : null,
                    'guid' => '',
                    'card_number' => $request->CardNumber,
                    'reference_code' => $data['ReferenceCode'],
                    'transaction_type' => 'Redeem',
                    'cashier' => $request->CashierRef,
                    'amount' => $request->Amount,
                    'processed_date' => date('Y-m-d H:i:s'),
                    'voided_date' => '',
                    'transaction_ref' => $request->TransactionRef,
                    'tip_amount' => $request->TipAmount,
                    'final_transaction_amount' => (float)$request->Amount + (float)$request->TipAmount,
                    'affects_card_value' => true,
                    'source_ip' => $ip_address,
                ]
            );
        } else {
            $data = [
                "Message" => null,
                "ReferenceCode" => $this->generateRandomString(8),
                "FinalTransactionAmount" => (float)$request->Amount + (float)$request->TipAmount,
                "CardBalance" => 0
            ];
            $transaction = \App\MerchantGiftCardAccountCardTransaction::create(
                [
                    'account_id' => $terminal ? $terminal->account_id : null,
                    'card_id' => null,
                    'terminal_id' => $terminal ? $terminal->id : null,
                    'guid' => '',
                    'card_number' => $request->CardNumber,
                    'reference_code' => $data['ReferenceCode'],
                    'transaction_type' => 'Redeem',
                    'cashier' => $request->CashierRef,
                    'amount' => $request->Amount,
                    'processed_date' => date('Y-m-d H:i:s'),
                    'voided_date' => '',
                    'transaction_ref' => $request->TransactionRef,
                    'tip_amount' => null,
                    'final_transaction_amount' => null,
                    'affects_card_value' => false,
                    'source_ip' => $ip_address
                ]
            );

            \Log::info([
                'ErrorCode' => 850,
                'Message' => 'The card specified is not active'
            ]);
            \App\MerchantGiftCardLog::create([
                'request_header' => json_encode($headers),
                'request_body' => json_encode($request->all()),
                'response_body' => json_encode([
                    'ErrorCode' => 850,
                    'Message' => 'The card specified is not active'
                ]),
                'transaction_type' => 'Redeem',
                'account_id' => $terminal->account_id,
            ]);
            return response()->json([
                'ErrorCode' => 850,
                'Message' => 'The card specified is not active'
            ],400);
        }



        if(!$transaction) {
            \Log::info([
                'ErrorCode' => 400,
                'Message' => 'Transaction Failed'
            ]);
            \App\MerchantGiftCardLog::create(
                [
                    'request_header' => json_encode($headers),
                    'request_body' => json_encode($request->all()),
                    'response_body' => json_encode([
                        'ErrorCode' => 400,
                        'Message' => 'Transaction Failed'
                    ]),
                    'transaction_type' => 'Redeem',
                    'account_id' => $terminal->account_id,
                ]
            );
            return response()->json([
                'ErrorCode' => 400,
                'Message' => 'Transaction Failed'
            ],400);
        }


        \Log::info('redeem response');
        \Log::info(response()->json($data, 200,
        ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE));
        // return response()->json($data);
        \App\MerchantGiftCardLog::create(
            [
                'request_header' => json_encode($headers),
                'request_body' => json_encode($request->all()),
                'response_body' => json_encode($data),
                'transaction_type' => 'Redeem',
                'account_id' => $terminal->account_id,
            ]
        );
        return response()->json($data, 200,
        ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }
    /**
         * @OA\Post(
         *     path="/POS/Void",
         *     summary="Voids the specified card",
         *     security={* {"passport": {}}, *},
            * @OA\RequestBody(
            *    required=true,
            *    description="The request.",
            *    @OA\JsonContent(
            *       required={"HardwareID","CardNumber","Amount","TipAmount","TransactionRef","CashierRef","ReferenceNumber","Force"},
            *       @OA\Property(property="HardwareID", type="string", example="TestHardWare1"),
            *       @OA\Property(property="CardNumber", type="string", example="TestCardNumber1"),
            *       @OA\Property(property="Amount", type="integer", example=0),
            *       @OA\Property(property="TipAmount", type="integer", example=0),
            *       @OA\Property(property="TransactionRef", type="string", example="TransactionRef4k109"),
            *       @OA\Property(property="CashierRef", type="string", example="CashierRef106"),
            *       @OA\Property(property="ReferenceNumber", type="string", example="ReferenceNumber105"),
            *       @OA\Property(property="Force", type="boolean", example=false),
            *    ),
            * ),
        *     @OA\Response(
        *          response="200",
        *          description="Success",
        *            @OA\JsonContent(
        *                  @OA\Property(property="Message", type="string"),
        *                  @OA\Property(property="ReferenceCode",type="string"),
        *                  @OA\Property(property="FinalTransaction",type="integer",example=0),
        *                  @OA\Property(property="CardBalance",type="integer",example=0),
        *              ),
        *     ),
        *     @OA\Response(
        *          response="401", description="Authorization has been denied for this request"
        *     ),
        *     @OA\Response(
        *          response="400", description="Business logic failure. Must process response body to determine details of failure reason.",
        *          @OA\JsonContent(
        *                 @OA\Property(property="ErrorCode",type="integer",example=0),
        *                 @OA\Property(property="Message",type="string"),
        *          )
        *     ),
        * )
        *
     */

    public function void(Request $request)
    {
        \Log::info(json_encode($request->fullUrl()));
        \Log::info(json_encode(getallheaders()));
        \Log::info(json_encode($request->all()));

        $headers = getallheaders();
        $ip_address = isset($headers['X-Real-IP']) ? $headers['X-Real-IP'] : '';

        $terminal = \App\MerchantGiftCardAccountTerminal::where('hardware_id',$request->HardwareID)->with('gift_card_account')->get()->first();
        // if(!$terminal) {
        //     return response()->json([
        //         'ErrorCode' => 400,
        //         'Message' => 'Terminal Does not exist'
        //     ]);
        // }
        // $account = $terminal->gift_card_account;
        $card = \App\MerchantGiftCardAccountCard::where('card_number',$request->CardNumber)->where('isActive',1)->orderBy('id','desc')->first();
        if($card) {
            $card->balance = (float)$card->balance + ((float)$request->Amount + (float)$request->TipAmount);

            if(!$card) {
                \Log::info([
                    'ErrorCode' => 400,
                    'Message' => 'Card Could not be created'
                ]);
                \App\MerchantGiftCardLog::create([
                    'request_header' => json_encode($headers),
                    'request_body' => json_encode($request->all()),
                    'response_body' => json_encode([
                        'ErrorCode' => 400,
                        'Message' => 'Card Could not be created'
                    ]),
                    'transaction_type' => 'VoidTransaction',
                    'account_id' => $terminal->account_id,
                ]);
                return response()->json([
                    'ErrorCode' => 400,
                    'Message' => 'Card Could not be created'
                ],400);
            }

            $data = [
                "Message" => null,
                "ReferenceCode" => $this->generateRandomString(8),
                "FinalTransactionAmount" => (float)$request->Amount + (float)$request->TipAmount,
                "CardBalance" => $card->balance
            ];

            $existing = \App\MerchantGiftCardAccountCardTransaction::where('card_number',$request->CardNumber)
                ->where('transaction_ref',$request->TransactionRef)
                ->where('amount',$request->Amount)
                ->where('transaction_type','VoidTransaction')
                ->get()
                ->first();
            if(!$existing) {
                $card->save();
            }

            $transaction = \App\MerchantGiftCardAccountCardTransaction::updateOrCreate(
                [
                    'id' => $existing ? $existing->id : null
                ],
                [
                    'account_id' => $card->account_id,
                    'card_id' => $card->id,
                    'terminal_id' => $terminal ? $terminal->id : null,
                    'guid' => '',
                    'card_number' => $request->CardNumber,
                    'reference_code' => $data['ReferenceCode'],
                    'transaction_type' => 'VoidTransaction',
                    'cashier' => $request->CashierRef,
                    'amount' => $request->Amount,
                    'processed_date' => date('Y-m-d H:i:s'),
                    'voided_date' => '',
                    'transaction_ref' => $request->TransactionRef,
                    'tip_amount' => $request->TipAmount,
                    'final_transaction_amount' => (float)$request->Amount + (float)$request->TipAmount,
                    'affects_card_value' => true,
                    'source_ip' => $ip_address,
                ]
            );
        } else {
            $data = [
                "Message" => null,
                "ReferenceCode" => $this->generateRandomString(8),
                "FinalTransactionAmount" => (float)$request->Amount + (float)$request->TipAmount,
                "CardBalance" => $card->balance
            ];
            $transaction = \App\MerchantGiftCardAccountCardTransaction::create(
                [
                    'account_id' => $terminal ? $terminal->account_id : null,
                    'card_id' => null,
                    'terminal_id' => $terminal ? $terminal->id : null,
                    'guid' => '',
                    'card_number' => $request->CardNumber,
                    'reference_code' => $data['ReferenceCode'],
                    'transaction_type' => 'VoidTransaction',
                    'cashier' => $request->CashierRef,
                    'amount' => $request->Amount,
                    'processed_date' => date('Y-m-d H:i:s'),
                    'voided_date' => '',
                    'transaction_ref' => $request->TransactionRef,
                    'tip_amount' => null,
                    'final_transaction_amount' => null,
                    'affects_card_value' => false,
                    'source_ip' => $ip_address
                ]
            );

            \Log::info([
                'ErrorCode' => 850,
                'Message' => 'The card specified is not active'
            ]);
            \App\MerchantGiftCardLog::create([
                'request_header' => json_encode($headers),
                'request_body' => json_encode($request->all()),
                'response_body' => json_encode([
                    'ErrorCode' => 850,
                    'Message' => 'The card specified is not active'
                ]),
                'transaction_type' => 'VoidTransaction',
                'account_id' => $terminal->account_id,
            ]);
            return response()->json([
                'ErrorCode' => 850,
                'Message' => 'The card specified is not active'
            ],400);
        }



        if(!$transaction) {
            \Log::info([
                'ErrorCode' => 400,
                'Message' => 'Transaction Failed'
            ]);
            \App\MerchantGiftCardLog::create(
                [
                    'request_header' => json_encode($headers),
                    'request_body' => json_encode($request->all()),
                    'response_body' => json_encode([
                        'ErrorCode' => 400,
                        'Message' => 'Transaction Failed'
                    ]),
                    'transaction_type' => 'VoidTransaction',
                    'account_id' => $terminal->account_id,
                ]

            );
            return response()->json([
                'ErrorCode' => 400,
                'Message' => 'Transaction Failed'
            ],400);
        }

        \App\MerchantGiftCardLog::create(
            [
                'request_header' => json_encode($headers),
                'request_body' => json_encode($request->all()),
                'response_body' => json_encode($data),
                'transaction_type' => 'VoidTransaction',
                'account_id' => $terminal->account_id,
            ]
        );
        return response()->json($data);

    }
    /**
         * @OA\Post(
         *     path="/POS/Return",
         *     summary="Returns the given amount to the specified card",
         *     security={* {"passport": {}}, *},
            * @OA\RequestBody(
            *    required=true,
            *    description="The request.",
            *    @OA\JsonContent(
            *       required={"HardwareID","CardNumber","Amount","TipAmount","TransactionRef","CashierRef","ReferenceNumber","Force"},
            *       @OA\Property(property="HardwareID", type="string", example="TestHardWare1"),
            *       @OA\Property(property="CardNumber", type="string", example="TestCardNumber1"),
            *       @OA\Property(property="Amount", type="integer", example=0),
            *       @OA\Property(property="TipAmount", type="integer", example=0),
            *       @OA\Property(property="TransactionRef", type="string", example="TransactionRef4k109"),
            *       @OA\Property(property="CashierRef", type="string", example="CashierRef106"),
            *       @OA\Property(property="ReferenceNumber", type="string", example="ReferenceNumber105"),
            *       @OA\Property(property="Force", type="boolean", example=false),
            *    ),
            * ),
        *     @OA\Response(
        *          response="200",
        *          description="Success",
        *            @OA\JsonContent(
        *                  @OA\Property(property="Message", type="string"),
        *                  @OA\Property(property="ReferenceCode",type="string"),
        *                  @OA\Property(property="FinalTransaction",type="integer",example=0),
        *                  @OA\Property(property="CardBalance",type="integer",example=0),
        *              ),
        *     ),
        *     @OA\Response(
        *          response="401", description="Authorization has been denied for this request"
        *     ),
        *     @OA\Response(
        *          response="400", description="Business logic failure. Must process response body to determine details of failure reason.",
        *          @OA\JsonContent(
        *                 @OA\Property(property="ErrorCode",type="integer",example=0),
        *                 @OA\Property(property="Message",type="string"),
        *          )
        *     ),
        * )
        *
     */
    public function return(Request $request)
    {


        \Log::info(json_encode($request->fullUrl()));
        \Log::info(json_encode(getallheaders()));
        \Log::info(json_encode($request->all()));

        $headers = getallheaders();
        $ip_address = isset($headers['X-Real-IP']) ? $headers['X-Real-IP'] : '';

        $terminal = \App\MerchantGiftCardAccountTerminal::where('hardware_id',$request->HardwareID)->with('gift_card_account')->get()->first();
        // if(!$terminal) {
        //     return response()->json([
        //         'ErrorCode' => 400,
        //         'Message' => 'Terminal Does not exist'
        //     ]);
        // }
        // $account = $terminal->gift_card_account;
        $card = \App\MerchantGiftCardAccountCard::where('card_number',$request->CardNumber)->where('isActive',1)->orderBy('id','desc')->first();
        if($card) {
            $card->balance = (float)$card->balance + ((float)$request->Amount + (float)$request->TipAmount);

            if(!$card) {
                \Log::info([
                    'ErrorCode' => 400,
                    'Message' => 'Card Could not be created'
                ]);
                \App\MerchantGiftCardLog::create([
                    'request_header' => json_encode($headers),
                    'request_body' => json_encode($request->all()),
                    'response_body' => json_encode([
                        'ErrorCode' => 400,
                        'Message' => 'Card Could not be created'
                    ]),
                    'transaction_type' => 'Return',
                    'account_id' => $terminal->account_id,
                ]);
                return response()->json([
                    'ErrorCode' => 400,
                    'Message' => 'Card Could not be created'
                ],400);
            }

            $data = [
                "Message" => null,
                "ReferenceCode" => $this->generateRandomString(8),
                "FinalTransactionAmount" => (float)$request->Amount + (float)$request->TipAmount,
                "CardBalance" => $card->balance
            ];

            $existing = \App\MerchantGiftCardAccountCardTransaction::where('card_number',$request->CardNumber)
                ->where('transaction_ref',$request->TransactionRef)
                ->where('amount',$request->Amount)
                ->where('transaction_type','Return')
                ->get()
                ->first();
            if(!$existing) {
                $card->save();
            }

            $transaction = \App\MerchantGiftCardAccountCardTransaction::updateOrCreate(
                [
                    'id' => $existing ? $existing->id : null
                ],
                [
                    'account_id' => $card->account_id,
                    'card_id' => $card->id,
                    'terminal_id' => $terminal ? $terminal->id : null,
                    'guid' => '',
                    'card_number' => $request->CardNumber,
                    'reference_code' => $data['ReferenceCode'],
                    'transaction_type' => 'Return',
                    'cashier' => $request->CashierRef,
                    'amount' => $request->Amount,
                    'processed_date' => date('Y-m-d H:i:s'),
                    'voided_date' => '',
                    'transaction_ref' => $request->TransactionRef,
                    'tip_amount' => $request->TipAmount,
                    'final_transaction_amount' => (float)$request->Amount + (float)$request->TipAmount,
                    'affects_card_value' => true,
                    'source_ip' => $ip_address,
                ]
            );
        } else {
            $data = [
                "Message" => null,
                "ReferenceCode" => $this->generateRandomString(8),
                "FinalTransactionAmount" => (float)$request->Amount + (float)$request->TipAmount,
                "CardBalance" => $card->balance
            ];
            $transaction = \App\MerchantGiftCardAccountCardTransaction::create(
                [
                    'account_id' => $terminal ? $terminal->account_id : null,
                    'card_id' => null,
                    'terminal_id' => $terminal ? $terminal->id : null,
                    'guid' => '',
                    'card_number' => $request->CardNumber,
                    'reference_code' => $data['ReferenceCode'],
                    'transaction_type' => 'Return',
                    'cashier' => $request->CashierRef,
                    'amount' => $request->Amount,
                    'processed_date' => date('Y-m-d H:i:s'),
                    'voided_date' => '',
                    'transaction_ref' => $request->TransactionRef,
                    'tip_amount' => null,
                    'final_transaction_amount' => null,
                    'affects_card_value' => false,
                    'source_ip' => $ip_address
                ]
            );

            \Log::info([
                'ErrorCode' => 850,
                'Message' => 'The card specified is not active'
            ]);
            \App\MerchantGiftCardLog::create([
                'request_header' => json_encode($headers),
                'request_body' => json_encode($request->all()),
                'response_body' => json_encode([
                    'ErrorCode' => 400,
                    'Message' => 'Card Could not be created'
                ]),
                'transaction_type' => 'Return',
                'account_id' => $terminal->account_id,
            ]);
            return response()->json([
                'ErrorCode' => 850,
                'Message' => 'The card specified is not active'
            ],400);
        }

        if(!$transaction) {
            \Log::info([
                'ErrorCode' => 400,
                'Message' => 'Transaction Failed'
            ]);
            \App\MerchantGiftCardLog::create(
                [
                    'request_header' => json_encode($headers),
                    'request_body' => json_encode($request->all()),
                    'response_body' => json_encode([
                        'ErrorCode' => 400,
                        'Message' => 'Transaction Failed'
                    ]),
                    'transaction_type' => 'Return',
                    'account_id' => $terminal->account_id,
                ]
            );
            return response()->json([
                'ErrorCode' => 400,
                'Message' => 'Transaction Failed'
            ],400);
        }

        \App\MerchantGiftCardLog::create(
            [
                'request_header' => json_encode($headers),
                'request_body' => json_encode($request->all()),
                'response_body' => json_encode($data),
                'transaction_type' => 'Return',
                'account_id' => $terminal->account_id,
            ]
        );
        return response()->json($data);

    }
    /**
         * @OA\Post(
         *     path="/POS/TransactionHistory",
         *     summary="Returns a history of transactions (max. 1 year) for the provided hardware id/account",
         *     security={* {"passport": {}}, *},
            * @OA\RequestBody(
            *    required=true,
            *    description="The request.",
            *    @OA\JsonContent(
            *       required={"HardwareID","StartDate","EndDate"},
            *       @OA\Property(property="HardwareID", type="string", example="TestHardWare1"),
            *       @OA\Property(property="StartDate", type="date", example="2020-11-11"),
            *       @OA\Property(property="EndDate", type="date", example="2020-11-11"),
            *    ),
            * ),
        *     @OA\Response(
        *          response="200",
        *          description="Success",
        *            @OA\JsonContent(
        *                   {}
        *              ),
        *     ),
        *     @OA\Response(
        *          response="401", description="Authorization has been denied for this request"
        *     ),
        *     @OA\Response(
        *          response="400", description="Business logic failure. Must process response body to determine details of failure reason.",
        *          @OA\JsonContent(
        *                 @OA\Property(property="ErrorCode",type="integer",example=0),
        *                 @OA\Property(property="Message",type="string"),
        *          )
        *     ),
        * )
        *
     */
    public function transactionHistory(Request $request)
    {


        \Log::info(json_encode($request->fullUrl()));
        \Log::info(json_encode(getallheaders()));
        \Log::info(json_encode($request->all()));

        $headers = getallheaders();
        $ip_address = isset($headers['X-Real-IP']) ? $headers['X-Real-IP'] : '';

        $terminal = \App\MerchantGiftCardAccountTerminal::where('hardware_id',$request->HardwareID)->with('gift_card_account')->get()->first();
        if(!$terminal) {
            \App\MerchantGiftCardLog::create([
                'request_header' => json_encode($headers),
                'request_body' => json_encode($request->all()),
                'response_body' => json_encode([
                    'success' => false,
                    'data' => 'Terminal Does not exist'
                ]),
                'transaction_type' => 'TransactionHistory',
                'account_id' => $terminal->account_id,
            ]);
            return response()->json([
                'success' => false,
                'data' => 'Terminal Does not exist'
            ]);
        }
        $terminal_id = $terminal->id;

        $transaction = \App\MerchantGiftCardAccountCardTransaction::select('reference_code','transaction_type','cashier','processed_date'
        )->where('terminal_id', $terminal_id)->whereBetween('processed_date', [$request->StartDate, $request->EndDate])->get();

        if($transaction) {
            \App\MerchantGiftCardLog::create([
                'request_header' => json_encode($headers),
                'request_body' => json_encode($request->all()),
                'response_body' => json_encode($transaction),
                'transaction_type' => 'TransactionHistory',
                'account_id' => $terminal->account_id,
            ]);
            return response()->json($transaction);
        }

    }
}
