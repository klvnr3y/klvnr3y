<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TooltipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $data = \App\Tooltip::all();
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
        //
        $data = \App\Tooltip::updateOrCreate([
            'id' => $request->id
        ], [
            'description' => isset($request->description) ? $request->description : null,
            'position' => $request->position,
            'selector' => $request->selector,
            'role' => auth()->user()->role,
            'tooltip_type' => $request->tooltip_type,
            'tooltip_color' => $request->tooltip_color,
            'insert_at' => $request->inserted_at,
            'video_url' => isset($request->video_url) ? $request->video_url : null,
            'is_req' => $request->is_req,
        ]);

        return response()->json([
            'success' => true,
            'data' => $request->all(),
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

        $data = \App\Tooltip::find($id);
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Tooltip with id ' . $id . ' not found'
            ], 400);
        }

        if ($data->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tooltip could not be deleted'
            ], 500);
        }
    }

    public function selector(Request $request)
    {

        $data = \App\Tooltip::where('selector', $request->selector)->where('role', $request->role)->get();
        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
}
