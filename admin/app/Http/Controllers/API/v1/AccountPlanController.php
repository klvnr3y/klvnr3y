<?php

namespace App\Http\Controllers\API\v1;

use App\AccountPlan;
use App\AccountType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $data = new AccountPlan;
        $data = $data->with('account_type');
        $data = $data->select([
            "account_plans.*",
        ]);
        $data = $data->where(function ($q) use ($request) {
            $q->orWhere('plan', 'LIKE', "%$request->filter_value%");
            $q->orWhere('description', 'LIKE', "%$request->filter_value%");
            $q->orWhere('amount', 'LIKE', "%$request->filter_value%");
            $q->orWhere('type', 'LIKE', "%$request->filter_value%");
        });

        if ($request->account_type_id) {
            $data = $data->where('account_type_id', $request->account_type_id);
        }

        if ($request->column && $request->order) {
            if (
                $request->column != '' && $request->column != 'undefined' && $request->column != 'null'  &&
                $request->order != ''  && $request->order != 'undefined' && $request->order != 'null'
            ) {
                $data = $data->orderBy(isset($request->column) ? $request->column : 'id', isset($request->order)  ? $request->order : 'desc');
            }
        } else {
            $data = $data->orderBy('index', 'asc');
        }

        if ($request->page_size) {
            $data = $data
                ->limit($request->page_size)
                ->paginate($request->page_size, ['*'], 'page', $request->page_number)->toArray();
        } else {
            $data = $data->get();
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
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
            'success' => false,
            'message' => 'Something went wrong'
        ];

        $findAccountType = AccountType::find($request->account_type_id);



        if ($findAccountType && $findAccountType->stripe_product_id) {
            $stripe_price_id = "";
            if (!$request->id) {
                $findAccountPlan = AccountPlan::with('account_type')->find($request->id);
                if ($findAccountPlan) {
                    $stripe_price_id = $findAccountPlan->stripe_price_id;
                }
            }

            $stripe_price = $this->stripe_price([
                'stripe_product_id' => $findAccountType->stripe_product_id,
                'stripe_price_id' => $stripe_price_id,
                'amount' => $request->amount,
                'metadata' => ['app_name' => 'Cancer Caregiver'],
                'type' => $request->type,
            ]);

            if ($stripe_price) {
                $accountPlan = AccountPlan::updateOrCreate([
                    'id' => $request->id
                ], [
                    'account_type_id' => $request->account_type_id,
                    'plan' => $request->plan,
                    'description' => $request->description,
                    'amount' => $request->amount,
                    'type' => $request->type,
                    'stripe_price_id' => $stripe_price['id'],
                ]);

                $ret = [
                    'success' => true,
                    'message' => 'Data ' . ($request->id ? 'updated' : 'saved') . ' successfully',
                    'data' => $accountPlan
                ];
            } else {
                $ret = [
                    'success' => false,
                    'message' => 'Something went wrong with the API',
                    "stripe_price" => $stripe_price
                ];
            }
        } else {
            $ret = [
                'success' => false,
                'message' => 'Please update Account Type and Policy tab first',
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
        $AccountPlan = AccountPlan::find($id);

        if (!$AccountPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $AccountPlan
        ], 200);
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
        $update_query = AccountPlan::find($id);

        $updated_result = $update_query->fill($request->all());
        $updated_result = $updated_result->save();

        if ($updated_result)
            return response()->json([
                'success'       => true,
                'message'       => 'Account Plan',
                'description'   => 'Data updated successfully'
            ], 200);
        else
            return response()->json([
                'success'       => false,
                'message'       => 'Account Plan',
                'description'   => 'Data not updated'
            ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $device = AccountPlan::find($id);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device with id ' . $id . ' not found'
            ], 400);
        }

        if ($device->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Device could not be deleted'
            ], 500);
        }
    }

    public function plan_sort(Request $request)
    {
        $sorted_data = json_decode($request->sorted_data);

        foreach ($sorted_data as $key => $row) {
            AccountPlan::updateOrCreate(
                ['id' => $row->id],
                [
                    'account_type_id' => $row->account_type_id,
                    'amount' => $row->amount,
                    'description' => $row->description,
                    'index' => $key,
                    'plan' => $row->plan,
                    'type' => $row->type,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'data' => $sorted_data
        ]);
    }
}