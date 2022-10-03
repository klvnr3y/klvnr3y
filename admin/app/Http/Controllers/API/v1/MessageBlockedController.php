<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\MessageBlocked;
use Illuminate\Http\Request;

class MessageBlockedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = MessageBlocked::with('blocked')->where('user_id', auth()->user()->id)->get();

        // $data = $data->where(function($q) use ($request) {
        //     $q->orWhere('user_id','LIKE',"%$request->filter_value%");
        //     $q->orWhere('primary','LIKE',"%$request->filter_value%");
        //     $q->orWhere('account_id','LIKE',"%$request->filter_value%");
        // });

        // if($request->column && $request->order) {
        //     if(
        //         $request->column != '' && $request->column != 'undefined' && $request->column != 'null'  &&
        //         $request->order != ''  && $request->order != 'undefined' && $request->order != 'null'
        //     ) {
        //         $data->orderBy(isset($request->column) ? $request->column : 'id', isset($request->order)  ? $request->order : 'desc');
        //     }
        // } else {
        //     $data->orderBy('id','desc');
        // }

        // $data= $data
        // ->limit($request->page_size)
        // ->paginate($request->page_size,['*'],'page',$request->page_number)->toArray();

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
        if ($request->action == 'block') {
            $message_blocked = MessageBlocked::updateOrCreate([
                'user_id' => $request->user_id,
                'blocked_id' => $request->blocked_id,
            ], [
                'user_id' => $request->user_id,
                'blocked_id' => $request->blocked_id,
            ]);
        } else {
            $message_blocked = MessageBlocked::where('blocked_id', $request->blocked_id)
                ->where('user_id', $request->user_id)
                ->first()
                ->delete();
        }


        return response()->json([
            'success' => true,
            'data' =>  $message_blocked
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
        $message_blocked = MessageBlocked::with('blocked')->where('user_id', $id)->get();
        if (!$message_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $message_blocked
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
        $update_query = MessageBlocked::find($id);

        $updated_result = $update_query->fill($request->all());
        $updated_result = $updated_result->save();

        if ($updated_result)
            return response()->json([
                'success'       => true,
                'message'       => 'Data',
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
        $message_blocked = MessageBlocked::find($id);

        if (!$message_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Data with id ' . $id . ' not found'
            ], 400);
        }

        if ($message_blocked->delete()) {
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