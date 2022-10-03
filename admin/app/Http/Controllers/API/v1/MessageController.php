<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = new Message();
        $user = auth()->user();

        $data = $data->where(function ($q) use ($request) {
            // $q->orWhere('account_type_id', 'LIKE', "%$request->filter_value%");
            // $q->orWhere('privacy_policy', 'LIKE', "%$request->filter_value%");
        });

        $data->where(function ($q) use ($user) {
            $q->orWhere('from_id', $user->id);
            $q->orWhere('to_id', $user->id);
        });

        $archived = \App\MessageArchived::where('user_id', $user->id)->get();
        $message_id_arr = [];
        if (count($archived) > 0) {
            $message_id_arr = array_column($archived->toArray(), 'message_id');
        }
        if ($request->status == 'Active') {
            $data->whereNotIn('id', $message_id_arr);
        }
        if ($request->status == 'Archived') {
            $data->whereIn('id', $message_id_arr);
        }
        // $data->where('message','init');

        // if($request->archived == 1) {
        // $data->where('archived', $request->archived);
        // } 
        // if($request->blocked) {
        // $data->where('blocked', $request->blocked);
        // }
        $data->with(['from', 'to']);

        if ($request->column && $request->order) {
            if (
                $request->column != '' && $request->column != 'undefined' && $request->column != 'null'  &&
                $request->order != ''  && $request->order != 'undefined' && $request->order != 'null'
            ) {
                if ($request->column == "order") {
                    # code...
                } else {
                    $data->orderBy(isset($request->column) ? $request->column : 'id', isset($request->order)  ? $request->order : 'desc');
                }
            }
        } else {
            $data->orderBy('updated_at', 'desc');
        }


        $data = $data->get();
        // $data = $data
        //     ->limit($request->page_size)
        //     ->paginate($request->page_size, ['*'], 'page', $request->page_number)->toArray();

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
        $message = \App\Message::orWhere(function ($q) use ($request) {
            $q->where('from_id', $request->from_id)
                ->where('to_id', $request->to_id);
        })
            ->orWhere(function ($q) use ($request) {
                $q->where('to_id', $request->from_id)
                    ->where('from_id', $request->to_id);
            })->first();

        $iamblocked = \App\MessageBlocked::where('user_id', $request->to_id)
            ->where('blocked_id', $request->from_id)
            ->first();
        if ($iamblocked) {
            return response()->json([
                'success' => false,
                'data' => "You are blocked by this user"
            ]);
        }
        if (!$message) {
            $message = Message::create([
                'from_id' => $request->from_id,
                'to_id' => $request->to_id,
                // 'message' => $request->message,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' =>  $message,
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
        $cities = Message::find($id);
        if (!$cities) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $cities
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
        $update_query = Message::find($id);

        $updated_result = $update_query->fill($request->all());
        $updated_result = $updated_result->save();

        if ($updated_result)
            return response()->json([
                'success'       => true,
                'message'       => $updated_result,
                'description'   => 'Data updated successfully'
            ], 200);
        else
            return response()->json([
                'success'       => false,
                'message'       => 'Data',
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
        $message = Message::find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Data with id ' . $id . ' not found'
            ], 400);
        }
        $message->archived = true;

        if ($message->save()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data could not be deleted'
            ], 500);
        }
    }

    /**
     * block the specified resource from storage.
     *
     * @param  \App\Message  $accountType
     * @return \Illuminate\Http\Response
     */
    public function block(Request $request)
    {
        $id = $request->id;
        $message = Message::find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Data with id ' . $id . ' not found'
            ], 400);
        }
        $message->blocked = true;

        if ($message->save()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data could not be deleted'
            ], 500);
        }
    }
}