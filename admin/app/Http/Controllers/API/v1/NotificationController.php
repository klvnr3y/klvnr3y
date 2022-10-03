<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;

use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = Notification::select([
            'notifications.*',
            DB::raw("(SELECT DATE_FORMAT(created_at, '%m/%d/%Y')) as created_str")
        ])->with(['user_notification']);

        $data = $data->where(function ($q) use ($request) {
            $q->orWhere('title', 'LIKE', "%$request->search%");
            $q->orWhere('description', 'LIKE', "%$request->search%");
            $q->orWhere('priority', 'LIKE', "%$request->search%");
            $q->orWhere('search_for', 'LIKE', "%$request->search%");
            $q->orWhere(DB::raw("(SELECT DATE_FORMAT(created_at, '%m/%d/%Y'))"), 'LIKE', "%$request->search%");
        });

        if ($request->sort_field && $request->sort_order) {
            if (
                $request->sort_field != '' && $request->sort_field != 'undefined' && $request->sort_field != 'null'  &&
                $request->sort_order != ''  && $request->sort_order != 'undefined' && $request->sort_order != 'null'
            ) {
                $data->orderBy(isset($request->sort_field) ? $request->sort_field : 'id', isset($request->sort_order)  ? $request->sort_order : 'desc');
            }
        } else {
            $data->orderBy('id', 'desc');
        }

        $data = $data
            ->limit($request->page_size)
            ->paginate($request->page_size, ['*'], 'page', $request->page)->toArray();

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
        $dataNotification = [
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'type' => $request->type,
        ];

        if ($request->id) {
            $dataNotification += ['updated_by' => auth()->user()->id];
        } else {
            $dataNotification += ['created_by' => auth()->user()->id];
        }

        $data = Notification::updateOrCreate(['id' => $request->id], $dataNotification);

        if (isset($request->id)) {
            if ($request->old_type != $request->type) {
                $delete = \App\UserNotification::where('notification_id', $data['id'])->count();
                if ($delete != 0) {
                    \App\UserNotification::where('notification_id', $data['id'])->delete();
                }
            }
        }

        if ($request->type === 'Both') {
            $findUser = \App\User::where('role', '!=', 'Admin')->get();

            foreach ($findUser as $value) {
                \App\UserNotification::updateOrCreate([
                    'user_id' => $value['id'],
                    'notification_id' => $data['id'],
                ], [
                    'user_id' => $value['id'],
                    'notification_id' => $data['id'],
                    'read' => 0,
                ]);
            }
        } else {
            $findUser = \App\User::where('role', $request->type)->get();

            foreach ($findUser as $value) {
                \App\UserNotification::updateOrCreate([
                    'user_id' => $value['id'],
                    'notification_id' => $data['id'],
                ], [
                    'user_id' => $value['id'],
                    'notification_id' => $data['id'],
                    'read' => 0,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Data " . ($request->id ? "updated" : "saved") . " successfully"
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Notification::find($id);
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $update_query = Notification::find($id);

        // $updated_result = $update_query->fill($request->all());
        // $updated_result = $updated_result->save();

        // if ($updated_result) {
        //     return response()->json([
        //         'success'       => true,
        //         'message'       => 'Data updated successfully',
        //     ], 200);
        // } else {
        //     return response()->json([
        //         'success'       => false,
        //         'message'       => 'Data not updated',
        //     ], 200);
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $ret = [
            'success' => false,
            'message' => 'Something went wrong'
        ];

        $data = Notification::find($id);

        if ($data) {
            if ($data->delete()) {
                $ret = [
                    'success' => true,
                    'message' => 'Data deleted successfully'
                ];
            } else {
                $ret = [
                    'success' => false,
                    'message' => 'Data could not be deleted'
                ];
            }
        } else {
            $ret = [
                'success' => false,
                'message' => 'Data not deleted'
            ];
        }

        return response()->json($ret, 200);
    }

    public function read(Request $request)
    {
        $update_query = \App\UserNotification::find($request->id);

        // $updated_result = $update_query->fill($request->all());
        $update_query->read = $request->read;
        $update_query = $update_query->save();

        return response()->json([
            'success' => true,
            'data' => $update_query
        ]);
    }

    public function archive(Request $request)
    {
        $update_query = \App\UserNotification::find($request->id);

        // $updated_result = $update_query->fill($request->all());
        $update_query->read = 1;
        $update_query->archive = 1;
        $update_query = $update_query->save();

        return response()->json([
            'success' => true,
            'data' => $update_query
        ]);
    }

    public function get_notification_alert(Request $request)
    {
        $data = \App\UserNotification::with(['notification', 'user'])
            ->where('user_id', auth()->user()->id)
            ->where('archive', '<>', 1)
            ->orderBy('created_at', 'desc')
            ->get();
        $unread = \App\UserNotification::where('user_id', auth()->user()->id)->where('archive', '<>', 1)->where('read', 0)->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'unread' => count($unread),
        ]);
    }

    public function get_messages_alert(Request $request)
    {
        $data = \App\MessageConvo::with(['from', 'to'])
            ->where(function ($q) {
                $q
                    ->orWhere('to_id', auth()->user()->id);
                // ->orWhere('from_id', auth()->user()->id);
            })
            ->where('unread', true)
            ->orderBy('created_at', 'desc')
            ->groupBy('message_id')
            ->get();
        // $unread = \App\MessageConvo::where('user_id', auth()->user()->id)->where('read', 0)->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'unread' => count($data),
        ]);
    }
}