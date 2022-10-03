<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_address = \App\UserAddress::where('user_id',  auth()->user()->id)->first();
        $data = new Ticket;
        $data = $data->select([
            "tickets.*",
            DB::raw("(DATE_FORMAT(tickets.created_at, '%m/%d/%Y')) as created_at_str"),
            DB::raw("(SELECT firstname FROM users WHERE users.id = tickets.assigned) as assigned_user"),
            DB::raw("(SELECT firstname FROM users WHERE users.id = tickets.requester) as requester_user"),
            DB::raw("(SELECT state FROM `user_addresses` WHERE user_addresses.user_id = tickets.requester) as requester_state"),

        ])->where(function ($q) use ($request) {
            $q->orWhere('subject', 'LIKE', "%$request->search%");
            $q->orWhere('comments', 'LIKE', "%$request->search%");
            $q->orWhere('status', 'LIKE', "%$request->search%");
            $q->orWhere('type', 'LIKE', "%$request->search%");
            $q->orWhere('priority', 'LIKE', "%$request->search%");
            $q->orWhere(DB::raw("(DATE_FORMAT(tickets.created_at, '%m/%d/%Y'))"), 'LIKE', "%$request->search%");
            $q->orWhere(DB::raw("(SELECT firstname FROM users WHERE users.id = tickets.assigned)"), 'LIKE', "%$request->search%");
            $q->orWhere(DB::raw("(SELECT firstname FROM users WHERE users.id = tickets.requester)"), 'LIKE', "%$request->search%");
        });
        if (auth()->user()->role == 'Admin') {
            $data = $data->where('id', "<>", "");
        } else if (auth()->user()->role == 'Manager') {
            $data->where(DB::raw("(SELECT state FROM `user_addresses` WHERE user_addresses.user_id = tickets.requester)"), $user_address['state']);
        } else {
            $data = $data->where('requester', auth()->user()->id);
        }

        if ($request->sort_field && $request->sort_order) {
            if (
                $request->sort_field != '' && $request->sort_field != 'undefined' && $request->sort_field != 'null'  &&
                $request->sort_order != ''  && $request->sort_order != 'undefined' && $request->sort_order != 'null'
            ) {
                if ($request->sort_field == "created_at_str") {
                    $data->orderBy(DB::raw("(DATE_FORMAT(tickets.created_at, '%m/%d/%Y'))"), isset($request->sort_order) ? $request->sort_order : 'desc');
                } else if ($request->sort_field == "assigned_user") {
                    $data->orderBy(DB::raw("(SELECT firstname FROM users WHERE users.id = tickets.assigned)"), isset($request->sort_order) ? $request->sort_order : 'desc');
                } else if ($request->sort_field == "requester") {
                    $data->orderBy(DB::raw("(SELECT firstname FROM users WHERE users.id = tickets.requester)"), isset($request->sort_order) ? $request->sort_order : 'desc');
                } else {
                    $data->orderBy(isset($request->sort_field) ? $request->sort_field : 'id', isset($request->sort_order)  ? $request->sort_order : 'desc');
                }
            }
        } else {
            $data->orderBy('id', 'asc');
        }

        $data = $data
            ->limit($request->page_size)
            ->paginate($request->page_size, ['*'], 'page', $request->page_number)->toArray();

        return response()->json([
            'success' => true,
            'data' => $data,
            // 'user_address' => $user_address['state'],
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

        $data = Ticket::create(
            [
                'subject' => $request->subject,
                'priority' => $request->priority,
                'comments' => $request->comments,
                'requester' => auth()->user()->id,
                'type' => "Other",
                'status' => auth()->user()->role == 'Admin' ? "Awaiting Customer Reply" : "Awaiting Support Reply",
            ]
        );


        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Ticket  $accountType
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Ticket::with([
            'requeter_user',
            'assigned_user',
            'ticket_response', 'ticket_response.user_submitted'
        ])->where('id', $id)->get();
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $data[0]
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ticket  $accountType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $update_query = Ticket::find($id);

        $updated_result = $update_query->fill($request->all());
        $updated_result = $updated_result->save();

        if ($updated_result) {
            return response()->json([
                'success'       => true,
                'message'       => 'Success',
                'description'   => 'Data updated successfully'
            ], 200);
        } else {
            return response()->json([
                'success'       => false,
                'message'       => 'Error',
                'description'   => 'Data not updated'
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ticket  $accountType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Ticket::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 400);
        }

        if ($data->delete()) {
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
