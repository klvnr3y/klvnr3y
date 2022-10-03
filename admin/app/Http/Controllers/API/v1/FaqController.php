<?php

namespace App\Http\Controllers\API\v1;

use App\Faq;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = new Faq();

        if ($request->role) {
            $data = $data->where(DB::raw("(SELECT type FROM account_types WHERE account_types.id = faqs.account_type_id)"), $request->role);
        }

        if ($request->account_type_id) {
            $data = $data->where('account_type_id', $request->account_type_id);
        }

        if ($request->search) {
            $data = $data->where(function ($q) use ($request) {
                $q->orWhere(DB::raw("(SELECT type FROM account_types WHERE account_types.id = faqs.account_type_id)"), 'LIKE', "%$request->search%");
                $q->orWhere('title', 'LIKE', "%$request->search%");
                $q->orWhere('description', 'LIKE', "%$request->search%");
                // $q->orWhere('index', 'LIKE', "%$request->search%");
            });
        }

        if ($request->sort_field && $request->sort_order) {
            if (
                $request->sort_field != '' && $request->sort_field != 'undefined' && $request->sort_field != 'null'  &&
                $request->sort_order != ''  && $request->sort_order != 'undefined' && $request->sort_order != 'null'
            ) {
                if ($request->sort_field == "title") {
                    //
                } else {
                    $data = $data->orderBy(isset($request->sort_field) ? $request->sort_field : 'id', isset($request->sort_order)  ? $request->sort_order : 'desc');
                }
            }
        } else {
            $data = $data->orderBy('index', 'asc');
        }

        if ($request->page_size) {
            $data = $data->limit($request->page_size)
                ->paginate($request->page_size, ['*'], 'page', $request->page_number)
                ->toArray();
        } else {
            $data = $data->get();
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'request' => $request->all()
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
        $device = Faq::updateOrCreate([
            'id' => $request->id
        ], [
            'account_type_id' => $request->account_type_id,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'data' =>  $device
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Faq = Faq::find($id);
        if (!$Faq) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $Faq
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
        $update_query = Faq::find($id);

        $updated_result = $update_query->fill($request->all());
        $updated_result = $updated_result->save();

        if ($updated_result)
            return response()->json([
                'success'       => true,
                'message'       => 'City',
                'description'   => 'Data updated successfully'
            ], 200);
        else
            return response()->json([
                'success'       => false,
                'message'       => 'City',
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
        $device = Faq::find($id);

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

    public function faq_sort(Request $request)
    {
        $sorted_data = json_decode($request->sorted_data);

        foreach ($sorted_data as $key => $row) {
            // $update_query = AccountTypePlan::find($row['id']);
            // $update_query->index =  $row['index'];
            // $update_query->save();

            $update_query = Faq::updateOrCreate(
                ['id' => $row->id],
                [
                    'account_type_id' => $row->account_type_id,
                    'title' => $row->title,
                    'description' => $row->description,
                    'index' => $key,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'data' => $sorted_data
        ]);
    }

    public function getFAQ(Request $request)
    {
        // $accID = \App\AccountType::where('account_type', $request->account_type)->first();
        $data = new Faq;
        // $data = $data->where('account_type_id', $accID['id']);
        if ($request->account_type) {
            $data = $data->where(DB::raw('(SELECT account_type FROM account_types WHERE id=account_type_id)'), $request->account_type);
        }
        $data = $data->orderBy('index', 'asc');
        $data = $data->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            // 'accID' => $accID['id']
        ], 200);
    }
}
