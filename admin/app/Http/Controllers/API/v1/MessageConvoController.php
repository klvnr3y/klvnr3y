<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Message;
use App\MessageConvo;
use Illuminate\Http\Request;

class MessageConvoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = new MessageConvo();
        $user = auth()->user();

        $data = $data->where(function ($q) use ($request) {
            // $q->orWhere('account_type_id', 'LIKE', "%$request->filter_value%");
            // $q->orWhere('privacy_policy', 'LIKE', "%$request->filter_value%");
        });

        $data->where(function ($q) use ($user) {
            $q->orWhere('from_id', $user->id);
            $q->orWhere('to_id', $user->id);
        });
        $data->where('message', 'init');

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
            $data->orderBy('id', 'desc');
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
        $message_convo = MessageConvo::create([
            'from_id' => $request->from_id,
            'to_id' => $request->to_id,
            'message_id' => $request->message_id,
            'message' => $request->message,
        ]);

        $message = \App\Message::find($request->message_id);
        $message->touch();

        if ($message) {
            $date = date("Y-m-d");
            $check = \App\MessageConvo::where('to_id', $request->to_id)->where('created_at', ">=", $date)->get();
            if (count($check) <= 1) {
                $user = \App\User::find($request->to_id);
                // $title = env('MIX_APP_NAME');

                // $editable_template = \App\EmailTemplate::where('title', 'You Have a New Instant Message Email')->first();
                // $subject = $editable_template->subject;
                // $subject = str_replace('{{firstname}}', $user->firstname, $subject);

                // $content = $editable_template->content;
                // $content = str_replace('{{firstname}}', $user->firstname, $content);

                // $data_email = [
                //     'to_name'   => $user->firstname . " " . $user->lastname,
                //     'to_email'  => $user->email,
                //     'subject' => $subject,
                //     'from_name' => $title,
                //     'from_email' => 'noreply@myceliya.com',
                //     'template'  => 'admin.emails.email_template',
                //     'body_data' => [
                //         'subject' => $subject,
                //         "htmlBody"   => $content,
                //     ]
                // ];
                // event(new \App\Events\SendMailEvent($data_email));

                $email_temp1 = explode('+', $user->email);
                $email_temp2 = explode('@', $user->email);

                $email_new = count($email_temp1) == 2 ? $email_temp1[0] . "@" . $email_temp2[1] : $email_temp1[0];
                $to_name = $user->firstname . " " . $user->lastname;

                $data = [
                    'to_name'       => $to_name,
                    'to_email'      => $email_new,
                ];
                $this->setup_email_template('Ticketing - Initial Ticket', $data);
            }
        }

        return response()->json([
            'success' => true,
            'data' =>  $message_convo
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\MessageConvo  $messageConvo
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $messages = Message::with(['message_convos' => function ($q) {
            $q->orderBy('created_at');
        }, 'message_convos.from', 'message_convos.to'])->find($id);


        if (!$messages) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 200);
        }

        foreach ($messages->message_convos as $key => $message_convo) {
            if ($message_convo['to_id'] == auth()->user()->id) {
                $message_convo->unread = 0;
                $message_convo->save();
            }
        }

        $iamblocked = \App\MessageBlocked::orWhere(function ($q) use ($messages) {
            $q->where('blocked_id', $messages->to_id);
            $q->where('user_id', $messages->from_id);
        })->orWhere(function ($q) use ($messages) {
            $q->where('blocked_id', $messages->from_id);
            $q->where('user_id', $messages->to_id);
        })->first();

        return response()->json([
            'success' => true,
            'data' => $messages,
            'iamblocked' => $iamblocked
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MessageConvo  $messageConvo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $update_query = MessageConvo::find($id);

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
                'message'       => 'City',
                'description'   => 'Data not updated'
            ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MessageConvo  $messageConvo
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $message = MessageConvo::find($id);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Device with id ' . $id . ' not found'
            ], 400);
        }

        if ($message->delete()) {
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
}