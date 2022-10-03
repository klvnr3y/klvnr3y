<?php

namespace App\Http\Controllers\API\v1;

use App\EmailTemplate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = EmailTemplate::all();

        return response()->json([
            'success' => true,
            'data' => $data
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
        if ($request->list) {
            foreach ($request->list as $value) {
                if (!empty($value['id'])) {
                    $EmailTemplate = EmailTemplate::find($value['id']);

                    $data = ['title' => $value['title']];

                    if (!empty($value['subject'])) {
                        $data['subject'] = $value['subject'];
                    }
                    if (!empty($value['body'])) {
                        $data['body'] = $value['body'];
                    }

                    $EmailTemplate->fill($data)->save();
                } else {
                    $data = ['title' => $value['title']];

                    if (!empty($value['subject'])) {
                        $data['subject'] = $value['subject'];
                    }
                    if (!empty($value['body'])) {
                        $data['body'] = $value['body'];
                    }
                    EmailTemplate::create($data);
                }
            }
        }

        $data = EmailTemplate::all();
        return response()->json([
            'success' => true,
            'data' => $data
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
    }
}