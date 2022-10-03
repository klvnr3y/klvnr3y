<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    /**
     * @OA\Post(
     *     path="/System/Version",
     *     summary="Returns the current system version number",
     *     security={* {"passport": {}}, *},
        * @OA\RequestBody(
        *    required=true,
        *    description="The request.",
        *    @OA\JsonContent(
        *              type="object",
        *    )
        * ),    
        *

     *     @OA\Response(
     *          response="200", 
     *          description="Success",
     *            @OA\JsonContent(
     *                  @OA\Property(property="Message", type="string"),
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
     public function version(Request $request)
     {
         return response()->json($request->all());
     }

      /**
     * @OA\Post(
     *     path="/System/ValidatedHardwareID",
     *     summary="Validates the terminal Hardware ID to ensure it's valid as entered on the client application",
     *     security={* {"passport": {}}, *},
        * @OA\RequestBody(
        *    required=true,
        *    description="The request.",
        *            @OA\JsonContent(
        *                  required={"Amount","HardwareID"},
        *                  @OA\Property(property="HardwareID", type="string"),
        *              ),
        * ),    
        *

     *     @OA\Response(
     *          response="200", 
     *          description="Success",
     *            @OA\JsonContent(
     *                  @OA\Property(property="Message", type="string"),
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
    public function validateHardwareId(Request $request)
    { 
        \Log::info(json_encode($request->fullUrl()));
        \Log::info(json_encode(getallheaders()));
        \Log::info(json_encode($request->all()));
        $merchant_gift_card_account_terminal = \App\MerchantGiftCardAccountTerminal::where('hardware_id', $request->HardwareID)->first();

        if($merchant_gift_card_account_terminal) {
            return response()->json([
                'message' => 'Hardware ID Found'
            ]);
        } else {
            return response()->json([
                'ErrorCode' => 400,
                'message' => 'Hardware ID Not Found'
            ]);
        }
        
    }


}
