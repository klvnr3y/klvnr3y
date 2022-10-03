<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InfoCheckBalanceController extends Controller
{ 
    /**
     * @OA\Post(
     *     path="/Info/Checkbalance",
     *     summary="Checks the balance",
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
    public function infoCheckBalance(Request $request)
    {
        
        \Log::info(json_encode($request->fullUrl()));
        \Log::info(json_encode(getallheaders()));
        \Log::info(json_encode($request->all()));
        
        $accountCard = \App\MerchantGiftCardAccountCard::where('card_number',$request->CardNumber)->orderBy('id','desc')->first();
        if($accountCard) {
            $data = [
                "Message" => "Balance Check",
                "ReferenceCode" => $request->ReferenceCode,
                "FinalTransactionAmount" => 0,
                "CardBalance" => $accountCard->balance
            ];

            $terminal = \App\MerchantGiftCardAccountTerminal::where('hardware_id',$request->HardwareID)->get()->first();
            if(!$terminal) {
                return response()->json([
                    'ErrorCode' => 400,
                    'Message' => 'Terminal Does not exist'
                ]);
            }

            if($request->TipAmount != 0) {
                return response()->json([
                    'ErrorCode' => 808,
                    'Message' => 'Tips may not be processed for this transaction type.'
                ]);
            }
            $transaction = \App\MerchantGiftCardAccountCardTransaction::create([
                'account_id' => $accountCard->account_id,
                'card_id' => $accountCard->id,
                'terminal_id' => $terminal->id,
                'card_number' => $request->CardNumber,
                'transaction_type' => 'BalanceCheck',
                'cashier' => $request->CashierRef,
                'amount' => $data['CardBalance'],
                'affects_card_value' => false,
            ]);

            if($transaction) {
                return response()->json($data);
            } else {
                return response()->json([
                    'ErrorCode' => 400,
                    'Message' => 'Something is wrong, contact administrator'
                ]);
            }

            



        } else {
            return response()->json([
                'ErrorCode' => 400,
                'Message' => 'Card Not Found'
            ]);
        }
    }
}
