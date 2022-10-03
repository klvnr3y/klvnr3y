<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\TicketResponse;
use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketResponseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = TicketResponse::with(['requeter_user', 'assigned_user'])
            ->where(function ($q) use ($request) {
                $q->orWhere('id', 'LIKE', "%$request->search%");
            });

        if ($request->state != '') {
            $data->where('state', $request->state);
        }

        if ($request->sort_order != '') {
            $data->orderBy($request->column, $request->order == 'ascend' ? 'asc' : 'desc');
        } else {
            $data->orderBy('id', 'desc');
        }

        $data = $data
            ->limit($request->page_size)
            ->paginate($request->page_size, ['*'], 'page', $request->page_number)->toArray();

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
        $data = new TicketResponse();
        $data->ticket_id = $request->ticket_id;
        $data->response = $request->response;
        $data->submitted_by = auth()->user()->id;
        $data->save();


        $ticket = Ticket::find($request->ticket_id);
        $ticket->status = auth()->user()->role == 'SUPER ADMIN' ? "Awaiting Customer Reply" : "Awaiting Support Reply";
        $ticket->save();


        if ($request->file('upload')) {
            $userImageFile = $request->file('upload');
            $userImageFileName = $userImageFile->getClientOriginalName();
            $userImageFilePath = time() . '_' . $userImageFile->getClientOriginalName();
            $userImageFilePath = $userImageFile->storeAs('uploads/ticket', $userImageFilePath, 'public');
            $userImageFileSize = $this->formatSizeUnits($userImageFile->getSize());

            $data->attachment_name = $userImageFileName;
            $data->attachment_url = 'storage/' . $userImageFilePath;
            $data->save();
        }

        return response()->json([
            'success' => true,
            'data' =>  $data,
            'message' =>  'Success',
            'description' =>  "Successfully replied!",
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
        $data = TicketResponse::find($id);
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
     * @param  \App\Ticket  $accountType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $update_query = TicketResponse::find($id);

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
     * @param  \App\Ticket  $accountType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = TicketResponse::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Date with id ' . $id . ' not found'
            ], 400);
        }

        if ($data->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Date could not be deleted'
            ], 500);
        }
    }
}
