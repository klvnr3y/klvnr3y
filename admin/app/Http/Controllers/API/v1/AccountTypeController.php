<?php

namespace App\Http\Controllers\API\v1;

use App\AccountType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = AccountType::with('account_plan');


        if ($request->filter_value) {
            $data = $data->where(function ($q) use ($request) {
                $q->orWhere('account_type', 'LIKE', "%$request->filter_value%");
                $q->orWhere('description', 'LIKE', "%$request->filter_value%");
            });
        }

        if ($request->column && $request->order) {
            if (
                $request->column != '' && $request->column != 'undefined' && $request->column != 'null'  &&
                $request->order != ''  && $request->order != 'undefined' && $request->order != 'null'
            ) {
                $data = $data->orderBy(isset($request->column) ? $request->column : 'id', isset($request->order)  ? $request->order : 'desc');
            }
        } else {
            $data = $data->orderBy('id', 'desc');
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

        $findAccountType = AccountType::find($request->id);

        $stripe_product = $this->stripe_product([
            'stripe_product_id' => $findAccountType->stripe_product_id,
            'product_name' => $request->type,
            'description' => $request->description,
            'metadata' => ['app_name' => 'Cancer Caregiver'],
        ]);

        if ($stripe_product) {
            $AccountType = AccountType::updateOrCreate([
                "id" => $request->id
            ], [
                "type" => $request->type,
                "description" => $request->description,
                "stripe_product_id" => $stripe_product['id']
            ]);

            if ($request->privacy_id) {
                $AccountType->privacy()->where('id', $request->privacy_id)->update(['privacy_policy' => $request->privacy_policy]);
            } else {
                $AccountType->privacy()->create(['privacy_policy' => $request->privacy_policy]);
            }

            $AccountTypes = AccountType::with(['privacy', 'faq'])->find($request->id);

            $ret = [
                'success' => true,
                'message' => 'Data ' . ($request->id ? 'updated' : 'saved') . ' successfully',
                'data' => $AccountTypes
            ];
        } else {
            $ret = [
                'success' => false,
                'message' => 'Something went wrong with the API'
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
        $AccountType = AccountType::with(['privacy', 'faq'])->find($id);
        if (!$AccountType) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $AccountType
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