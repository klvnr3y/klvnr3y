<?php

namespace App\Http\Controllers\API\v1;

use App\AthleteOrganization;
use App\AthleteWebsite;
use App\CoachOrganization;
use App\Company;
use App\Http\Controllers\Controller;
use App\Module;
use App\Organization;
use Illuminate\Http\Request;
use App\User;
use App\UserAddress;
use App\UserPayment;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = User::select([
            "users.*",
            DB::raw("(SELECT IF(firstname IS NOT NULL, firstname, CONCAT(`firstname`, ' ', `lastname`))) `name`"),
            DB::raw("(SELECT country FROM `user_addresses` WHERE user_addresses.user_id = users.id) `country`"),
            DB::raw("(SELECT state FROM `user_addresses` WHERE user_addresses.user_id = users.id) `state`"),
            DB::raw("(SELECT city FROM `user_addresses` WHERE user_addresses.user_id = users.id) `city`"),
        ]);

        if ($request->search) {
            $data = $data->where(function ($q) use ($request) {
                $q->orWhere('role', 'LIKE', "%$request->search%");
                $q->orWhere("firstname", 'LIKE', "%$request->search%");
                $q->orWhere("lastname", 'LIKE', "%$request->search%");
                $q->orWhere(DB::raw("(SELECT IF(firstname IS NOT NULL, firstname, CONCAT(`firstname`, ' ', `lastname`)))"), 'LIKE', "%$request->search%");
                $q->orWhere(DB::raw("(SELECT country FROM `user_addresses` WHERE user_addresses.user_id = users.id)"), 'LIKE', "%$request->search%");
                $q->orWhere(DB::raw("(SELECT state FROM `user_addresses` WHERE user_addresses.user_id = users.id)"), 'LIKE', "%$request->search%");
                $q->orWhere(DB::raw("(SELECT city FROM `user_addresses` WHERE user_addresses.user_id = users.id)"), 'LIKE', "%$request->search%");
            });
        }

        if (isset($request->status)) {
            $data = $data->where('status', $request->status);
        }

        if (isset($request->role)) {
            $data = $data->where('role', $request->role);
        }

        if (auth()->user()->role == 'Admin') {
            # code...
        } else {
            if (isset($request->from)) {
                $data = $data->where('role', '<>', 'Admin');
            }
        }

        if (isset($request->for_messages)) {
            $blocklist = \App\MessageBlocked::where('user_id', auth()->user()->id)->get()->toArray();
            $data->whereNotIn('id', array_column($blocklist, 'blocked_id'));
        }

        if ($request->sort_field && $request->sort_order) {
            if (
                $request->sort_field != '' && $request->sort_field != 'undefined' && $request->sort_field != 'null'  &&
                $request->sort_order != ''  && $request->sort_order != 'undefined' && $request->sort_order != 'null'
            ) {
                if ($request->sort_field == "name") {
                    $data->orderBy(DB::raw("(SELECT IF(firstname IS NOT NULL, firstname, CONCAT(`firstname`, ' ', `lastname`)))"), isset($request->sort_order)  ? $request->sort_order : 'desc');
                } else if ($request->sort_field == "country") {
                    $data->orderBy(DB::raw("(SELECT country FROM `user_addresses` WHERE user_addresses.user_id = users.id)"), isset($request->sort_order)  ? $request->sort_order : 'desc');
                } else if ($request->sort_field == "state") {
                    $data->orderBy(DB::raw("(SELECT state FROM `user_addresses` WHERE user_addresses.user_id = users.id)"), isset($request->sort_order)  ? $request->sort_order : 'desc');
                } else if ($request->sort_field == "city") {
                    $data->orderBy(DB::raw("(SELECT city FROM `user_addresses` WHERE user_addresses.user_id = users.id)"), isset($request->sort_order)  ? $request->sort_order : 'desc');
                } else {
                    $data->orderBy(isset($request->sort_field) ? $request->sort_field : 'id', isset($request->sort_order)  ? $request->sort_order : 'desc');
                }
            }
        } else {
            $data->orderBy('id', 'desc');
        }

        if ($request->page_size) {
            $data = $data
                ->limit($request->page_size)
                ->paginate($request->page_size, ['*'], 'page', $request->page)->toArray();
        } else {
            $data = $data->get();
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'search' => $request->search,
            'role' => $request->role,
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
        $update_query = User::find($id);

        $updated_result = $update_query->fill([
            "firstname" => $request->firstname,
            "lastname" => $request->lastname,
            "contact_number" => $request->contact_number,
        ])->save();

        if ($updated_result) {
            return response()->json([
                'success'       => true,
                'message'       => 'User',
                'description'   => 'Data updated successfully'
            ], 200);
        } else {
            return response()->json([
                'success'       => false,
                'message'       => 'User',
                'description'   => 'Data not updated'
            ], 200);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = User::with(['user_address'])->find($id);
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    public function user_password(Request $request)
    {
        $current_password = Hash::make($request->current_password);
        $new_password = $request->new_password;
        $check = User::where('id', $request->id)->first();

        if ($check) {
            if (Hash::check($request->current_password, $check->password)) {
                $check->password = Hash::make($new_password);
                $check->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Success',
                    'description' => 'Successfully change password!'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error',
                    'description' => 'Current password mismatch!'
                ], 200);
            }
        }
    }

    public function update_profile(Request $request)
    {
        $ret = [
            "success" => false,
            "message" => "Something went wrong",
            "request" => $request->all()
        ];

        $user = User::with(['user_address'])
            ->find($request->id);

        if ($user) {
            $data_user = [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'contact_number' => $request->contact_number,
            ];

            if ($request->email_alternative) {
                $data_user += ['email_alternative' => $request->email_alternative];
            }

            if ($request->file('profile_image')) {
                $userImageFile = $request->file('profile_image');
                $userImageFilePath = time() . '_' . $userImageFile->getClientOriginalName();
                $userImageFilePath = $userImageFile->storeAs('uploads/profile_image', $userImageFilePath, 'public');

                $data_user += ['profile_image' => 'storage/' . $userImageFilePath];
            }

            $updated = $user->fill($data_user);
            $updated->save();


            $ret = [
                "success" => true,
                "message" => "Data updated successfully",
                "data" => $user,
            ];
        } else {
            $ret = [
                "success" => false,
                "message" => "Data not updated"
            ];
        }

        return response()->json($ret, 200);
    }

    public function uppdate_profile_image(Request $request)
    {
        $data = User::find($request->id);

        if ($request->file('upload')) {
            $userImageFile = $request->file('upload');
            $userImageFileName = $userImageFile->getClientOriginalName();
            $userImageFilePath = time() . '_' . $userImageFile->getClientOriginalName();
            $userImageFilePath = $userImageFile->storeAs('uploads/profile_image', $userImageFilePath, 'public');
            $userImageFileSize = $this->formatSizeUnits($userImageFile->getSize());

            $data->profile_image = 'storage/' . $userImageFilePath;
            $data->save();
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Success',
            'description' => 'Successfully save!'
        ], 200);
    }

    public function user_deactive(Request $request)
    {
        $data = User::find($request->id);

        if ($data) {
            $data->fill(['status' => 'Deactive'])->save();

            return response()->json([
                'success' => true,
                'message' => 'Data deactivate successfully',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data not deactivated',
            ], 200);
        }
    }
    public function user_reactive(Request $request)
    {
        $data = User::find($request->id);

        if ($data) {
            $data->fill(['status' => 'Active'])->save();

            return response()->json([
                'success' => true,
                'message' => 'Data reactivate successfully',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data not reactivated',
            ], 200);
        }
    }

    public function users_activated(Request $request)
    {
        foreach ($request->selected as $value) {
            User::where('id', $value)->update(['status' => 'Active']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Selected data activated successfully'
        ], 200);
    }

    public function users_deactivated(Request $request)
    {
        foreach ($request->selected as $value) {
            User::where('id', $value)->update(['status' => 'Deactive']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Selected data deactivated successfully'
        ], 200);
    }

    public function user_assigned_tickets(Request $request)
    {
        $data = User::all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    public function profile_change_password(Request $request)
    {
        $ret = [
            "success" => false,
            "message" => "Something went wrong"
        ];

        $findUser = User::find($request->id);

        if ($findUser) {
            if (Hash::check($request->password_old, $findUser->password)) {
                $findUserUpdate = $findUser->fill(['password' => Hash::make($request->password_1)]);
                $findUserUpdate->save();

                $ret = [
                    "success" => true,
                    "message" => "Password change successfully"
                ];
            } else {
                $ret = [
                    "success" => false,
                    "message" => "Old password did not match"
                ];
            }
        } else {
            $ret = [
                "success" => false,
                "message" => "Could not find account"
            ];
        }

        return response()->json($ret, 200);
    }

    public function edit_permission(Request $request)
    {
        $user = User::find($request->id);

        if ($user) {
            $updated = $user->fill([
                'edit_permission' => $request->edit_permission,
            ]);

            if ($updated->save()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User',
                    'description' => 'Edit Permission Successfully Updated!'
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User',
                'description' => 'Edit Permission Not Updated!'
            ], 200);
        }
    }


    public function message_find_user(Request $request)
    {
        $data = new User;

        if ($request->search) {
            $data = $data->where(function ($q) use ($request) {
                $q->orWhere('role', 'LIKE', "%$request->search%");
                $q->orWhere("firstname", 'LIKE', "%$request->search%");
                $q->orWhere("lastname", 'LIKE', "%$request->search%");
            });
        }

        // if (isset($request->for_messages)) {
        //     $blocklist = \App\MessageBlocked::where('user_id', auth()->user()->id)->get()->toArray();
        //     $data->whereNotIn('id', array_column($blocklist, 'blocked_id'));
        // }

        $blocklist = \App\MessageBlocked::where('user_id', auth()->user()->id)->get()->toArray();
        // $data->whereNotIn('id', array_column($blocklist, 'blocked_id'));
        if ($blocklist) {
            $data = $data->whereNotIn('id', array_column($blocklist, 'blocked_id'));
        }

        if (isset($request->role)) {
            $data = $data->where('role', $request->role);
        }

        $data = $data->get();

        return response()->json([
            'success' => true,
            'users' => $data,
            'block' => $blocklist,
            'block1' => array_column($blocklist, 'blocked_id'),
            'request' => $request->all()
        ], 200);
    }

    public function get_by_id(Request $request)
    {
        if ($request->id) {
            $data = User::where('id', $request->id)->get();
            return response()->json([
                'success' => true,
                'data' => $data,
            ], 200);
        }
    }

    public function invite_people(Request $request)
    {
        $ret = [
            'success' => false,
            'message' => 'Something went wrong',
            'request' => $request->all()
        ];

        if (isset($request->list)) {
            if (count($request->list) > 0) {
                $by_name = auth()->user()->firstname;

                $account_plans = \App\AccountPlan::with('account_type')->where('account_type_id', $request->account_type_id)->first();

                foreach ($request->list as $value) {
                    $email_temp1 = explode('+', $value['email']);
                    $email_temp2 = explode('@', $value['email']);

                    $email_new = count($email_temp1) == 2 ? $email_temp1[0] . "@" . $email_temp2[1] : $email_temp1[0];

                    // $default = urlencode('https://ui-avatars.com/api/' . $value->firstname . '/100/0D8ABC/fff/2/0/1');
                    // $img =  'https://www.gravatar.com/avatar/' . md5(strtolower(trim($value->email))) . '?d=' . $default;

                    // $dataUser = [
                    //     'firstname' => $value->firstname,
                    //     'lastname' => $value->lastname,
                    //     'email' => $value->email,
                    //     'username' => $email_temp2[0],
                    //     'role' => $account_plans->account_type->type,
                    //     'status' => 'Active',
                    //     'profile_image' => $img,
                    //     // 'stripe_customer_id'    => $stripe_customer_subscription['customer']['id']
                    // ];

                    // User::create($dataUser);

                    $data = [
                        'by_name'       => $by_name,
                        'to_name'       => $value['firstname'] . ' ' . $value['lastname'],
                        'to_email'      => $email_new,
                        'link_origin'   => $request->link_origin,
                        'link'          => $request->link_origin . '/register' . '/' . $request->bearerToken(),
                    ];
                    $this->setup_email_template('Invite People', $data);
                }

                $ret = [
                    'success' => true,
                    'message' => 'Invite email sent successfully',
                ];
            } else {
                $ret = [
                    'success' => false,
                    'message' => 'Please add email',
                ];
            }
        }

        return response()->json($ret, 200);
    }

    public function member_options(Request $request)
    {
        $data = User::where('role', '<>', 'Admin')->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
}