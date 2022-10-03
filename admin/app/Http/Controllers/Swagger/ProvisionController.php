<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProvisionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/Provision/Procure",
     *     summary="Procure a gift card (with optional SMS and email notification for beneficiary)",
     *     security={* {"passport": {}}, *},
        * @OA\RequestBody(
        *    required=true,
        *    description="The request.",
        *    @OA\JsonContent(
        *        required={"Amount"},
        *        @OA\Property(property="Amount",  type="integer"  ),
        *        @OA\Property(
        *        property="SMS",
        *        type="object",
        *                @OA\Property(property="Number", type="string" ),
        *                @OA\Property(property="Sender", type="string"),
        *                @OA\Property(property="Recipient", type="string" ),
        *                @OA\Property(property="Message", type="string"),
        *       ),
        *        @OA\Property(
        *        property="Email",
        *        type="object",
        *           @OA\Property(
        *              type="object",
        *              property="From",
        *              @OA\Property(property="Name", type="string" ),
        *              @OA\Property(property="Address", type="string"),
        *           ),
        *           @OA\Property(
        *              type="object",
        *              property="To",
        *              @OA\Property(property="Name", type="string" ),
        *              @OA\Property(property="Address", type="string"),
        *           ),
        *        @OA\Property(property="Subject",  type="string"  ),
        *        @OA\Property(property="Template",  type="string"  )
        *       ),
        *       @OA\Property(property="CustomerReference",  type="integer"  )
        *    ),
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
     public function procure(Request $request)
     {

        
        \Log::info(json_encode($request->fullUrl()));
        \Log::info(json_encode(getallheaders()));
        \Log::info(json_encode($request->all()));
        
         return response()->json($request->all());
     }

      /**
     * @OA\Post(
     *     path="/Provision/ResendSMS",
     *     summary="Resend a failed SMS request",
     *     security={* {"passport": {}}, *},
        * @OA\RequestBody(
        *    required=true,
        *    description="The request.",
        *    @OA\JsonContent(
        *        required={"ReferenceCode"},
        *        @OA\Property(property="ReferenceCode",  type="string"  ),
        *    ),
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
    public function resendSMS(Request $request)
    {

        
        \Log::info(json_encode($request->fullUrl()));
        \Log::info(json_encode(getallheaders()));
        \Log::info(json_encode($request->all()));
        
        return response()->json($request->all());
    }

     /**
     * @OA\Post(
     *     path="/Provision/AddValue",
     *     summary="Add a value to a gift card.",
     *     security={* {"passport": {}}, *},
        * @OA\RequestBody(
        *    required=true,
        *    description="The request.",
        *    @OA\JsonContent(
        *        required={"Amount","CardNumber"},
        *        @OA\Property(property="CardNumber",  type="string"  ),
        *        @OA\Property(property="Amount",  type="integer"  ),
        *        @OA\Property(
        *        property="SMS",
        *        type="object",
        *                @OA\Property(property="Number", type="string" ),
        *                @OA\Property(property="Sender", type="string"),
        *                @OA\Property(property="Recipient", type="string" ),
        *                @OA\Property(property="Message", type="string"),
        *       ),
        *        @OA\Property(
        *        property="Email",
        *        type="object",
        *           @OA\Property(
        *              type="object",
        *              property="From",
        *              @OA\Property(property="Name", type="string" ),
        *              @OA\Property(property="Address", type="string"),
        *           ),
        *           @OA\Property(
        *              type="object",
        *              property="To",
        *              @OA\Property(property="Name", type="string" ),
        *              @OA\Property(property="Address", type="string"),
        *           ),
        *        @OA\Property(property="Subject",  type="string"  ),
        *        @OA\Property(property="Template",  type="string"  )
        *       ),
        *       @OA\Property(property="CustomerReference",  type="integer"  )
        *    ),
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
    public function addValue(Request $request)
    {

        
        \Log::info(json_encode($request->fullUrl()));
        \Log::info(json_encode(getallheaders()));
        \Log::info(json_encode($request->all()));
        
        return response()->json($request->all());
    }

}
