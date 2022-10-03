<?php

namespace App\Http\Controllers\API\v1;

use App\AccountPlan;
use App\Http\Controllers\Controller;
use App\UserPayment;
use App\UserPlan;
use Illuminate\Http\Request;

class UserPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ret = [
            "success" => false,
            "message" => "Something went wrong"
        ];

        $findAccountPlan  = AccountPlan::find($request->type);

        if ($findAccountPlan) {
            $userPlan = UserPlan::create([
                "user_id" => $request->user_id,
                "account_plan_id" => $request->type
            ]);

            UserPayment::create([
                "user_id" => $request->user_id,
                "user_plan_id" => $userPlan->id,
                "invoice_id" => $this->generate_invoice($request->user_id),
                "description" => "Update Subscription",
                "amount" => $findAccountPlan->amount,
                "date_paid" => date("Y-m-d")
            ]);

            $findUser = \App\User::find($request->user_id);

            if ($findUser) {
                $findUserUpdate = $findUser->fill(["role" => $findAccountPlan->plan]);
                $findUserUpdate->save();
            }

            $ret = [
                "success" => true,
                "message" => "Subscription updated successfully",
                "data" => $findUser
            ];
        } else {
            $ret = [
                "success" => false,
                "message" => "Could not find plan selected, please contact admin."
            ];
        }

        return response()->json($ret, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}