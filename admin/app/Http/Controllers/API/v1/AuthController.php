<?php

namespace App\Http\Controllers\API\v1;

use App\AthleteInfo;
use App\EmailTemplate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Auth;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\HistoricalPasswordCount;
use App\UserCard;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $ret = [
            "success" => true,
            "message" => "Something went wrong!",
            // 'request' => $request->all(),
            // 'auth' => Auth::guard('api')->user()
        ];

        $checkEmail = User::where('email', $request->email)->count();

        if ($checkEmail == 0) {
            if (isset($request->account_type_id)) {
                $account_plans = \App\AccountPlan::with('account_type')->where('account_type_id', $request->account_type_id)->first();

                $stripe_customer_subscription = $this->stripe_customer_subscription([
                    'metadata' => [
                        'app_name' => "Cancer Caregiver",
                        'coupon' => $request->coupon,
                        'coupon_type' => "Registration Payment",
                        'plan' => $account_plans->plan,
                    ],
                    'description' => 'Cancer Caregiver Customer',
                    'email' => $request->email,
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'credit_card_name' => $request->credit_card_name,
                    'credit_card_number' => $request->credit_card_number,
                    'credit_expiry' => $request->credit_expiry,
                    'credit_cvv' => $request->credit_cvv,
                    'billing_street_address1' =>   $request->billing_street_address1,
                    'billing_street_address2' =>  $request->billing_street_address2,
                    'billing_city' => $request->billing_city,
                    'billing_country' => $request->billing_country,
                    'billing_zip' => $request->billing_zip,
                    'billing_state' => $request->billing_state,
                    // 'amount' => $account_plans->amount,
                    'stripe_price_id' => $account_plans->stripe_price_id
                ]);

                if ($stripe_customer_subscription['customer']) {
                    $default = urlencode('https://ui-avatars.com/api/' . $request->firstname . '/100/0D8ABC/fff/2/0/1');
                    $img =  'https://www.gravatar.com/avatar/' . md5(strtolower(trim($request->email))) . '?d=' . $default;

                    $data = [
                        'email'                 => $request->email,
                        'username'              => $request->username,
                        'firstname'             => $request->firstname,
                        'lastname'              => $request->lastname,
                        'role'                  => $account_plans->account_type->type,
                        'status'                => 'Active',
                        'one_time_modal'        => 0,
                        'profile_image'         => $img,
                        'stripe_customer_id'    => $stripe_customer_subscription['customer']['id']
                    ];

                    if (!empty(Auth::guard('api')->user())) {
                        $data += [
                            "referred_by" => Auth::guard('api')->user()->id,
                            "referred_at" => date("Y-m-d")
                        ];
                    }

                    $userCreate = User::create($data);

                    if ($userCreate) {
                        $dataUserAddress = [
                            "country"       => $request->country,
                            "state"         => $request->state,
                            "zip_code"      => $request->zip,
                            "is_primary"    => 1
                        ];
                        $userCreate->user_address()->create($dataUserAddress);

                        $dataBillingAddress = [
                            "address1"  => $request->billing_street_address1,
                            "address2"  => $request->billing_street_address2,
                            "country"   => $request->billing_country,
                            "city"      => $request->billing_city,
                            "state"     => $request->billing_state,
                            "zip_code"  => $request->billing_zip,
                            "is_primary"    => 1
                        ];
                        $userCreate->user_billing_address()->create($dataBillingAddress);

                        $dataUserPlan = \App\UserPlan::create([
                            'user_id' => $userCreate->id,
                            'account_plan_id' => $account_plans->id,
                            'stripe_subscription_id' => $stripe_customer_subscription['subscription']['id'],
                            'stripe_subscription_status' => $stripe_customer_subscription['subscription']['status'],
                        ]);

                        \App\UserPayment::create([
                            'user_id' => $userCreate->id,
                            'user_plan_id' => $dataUserPlan->id,
                            'invoice_id' => '00001',
                            // 'charge_id' => $stripe_customer_charge['charge']->id,
                            // 'from_url' => 'register',
                            'amount' => $request->amount,
                            'date_paid' => date('Y-m-d'),
                            'description' => 'Registration Payment'
                        ]);

                        $token = $userCreate->createToken('cancer-caregiver')->accessToken;

                        $email_temp1 = explode('+', $request->email);
                        $email_temp2 = explode('@', $request->email);

                        $email_new = count($email_temp1) == 2 ? $email_temp1[0] . "@" . $email_temp2[1] : $email_temp1[0];
                        $to_name = $request->firstname . " " . $request->lastname;

                        $data_setup_email_template = [
                            'to_name'       => $to_name,
                            'to_email'      => $email_new,
                            'link_origin'   => $request->link_origin,
                            'token'         => $token,
                            'username'      => $request->username,
                            'link'          => $request->link_origin . '/register/setup-password/' . $token,
                        ];
                        $this->setup_email_template("Registration - Success", $data_setup_email_template);

                        $ret = [
                            "success" => true,
                            "message" => "Successfully registered!",
                        ];
                    }
                } else {
                    $ret = [
                        "success" => false,
                        "message" => $stripe_customer_subscription,
                    ];
                }
            }
        } else {
            $ret = [
                "success" => false,
                "message" => "Email already registered!",
            ];
        }

        return response()->json($ret, 200);
    }


    /**
     * Handles Set Password Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function set_password(Request $request)
    {
        $ret = [
            "success" => false,
            "message" => "Something went wrong!",
        ];

        $userData =  Auth::guard('api')->user();

        $data = [
            'password'          => Hash::make($request->password),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10)
        ];

        $findUser = User::where("id", $userData->id)->first();

        if ($findUser) {
            if ($findUser->email_verified_at == "") {
                if ($request->password != '') {
                    if ($findUser->fill($data)->save()) {
                        $token = $findUser->createToken('cancer-caregiver')->accessToken;

                        $ret = [
                            "success" => true,
                            "message" => "Successfully setup password!",
                            "authUser" => [
                                'data' => $findUser,
                                'token' => $token,
                            ]
                        ];
                    }
                } else {
                    $ret = [
                        "success" => false,
                        "message" => "",
                        "already_verified" => false,
                    ];
                }
            } else {
                $ret = [
                    "success" => false,
                    "message" => "Email Already Verified!",
                    "already_verified" => true,
                ];
            }
        } else {
            $ret = [
                "success" => false,
                "message" => "Token Expired!",
            ];
        }

        return response()->json($ret, 200);
    }

    /**
     * Handles Login Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $credentials = [
                'email' => $request->email,
                'password' => $request->password
            ];
        } else {
            $credentials = [
                'username' => $request->email,
                'password' => $request->password
            ];
        }

        if (Auth::guard('web')->attempt($credentials)) {
            $user = Auth::guard('web')->user();
            if ($user->status == 'Active') {
                $token = $user->createToken('cancer-caregiver')->accessToken;

                return response()->json([
                    'success' => true,
                    'data' => $user,
                    'token' => $token,

                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is not yet verified!',
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error',
                'description' => 'Unrecognized username or password. <b>Forgot your password?</b>',
            ], 401);
        }
    }

    /**
     * Returns Authenticated User Details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details()
    {
        return response()->json(['user' => auth()->user()], 200);
    }

    public function registrationVerify(Request $request)
    {
        $user = Auth::guard('api')->user();
        $user->status = 'Active';
        // $user->email_verified_at = date('Y-m-d H:i:s');
        $user->save();

        return response()->json([
            'success' => true
        ]);
    }

    public function verify(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|min:6',
        ]);
        $user =  Auth::guard('api')->user();

        $hpc = HistoricalPasswordCount::create([
            'user_id' => $user->id,
            'password' => Hash::make($request->password)

        ]);

        $user->status = 'Active';
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true
        ]);
    }

    public function auth(Request $request)
    {
        return response()->json(['success' => true], 200);
    }

    public function logout(Request $request)
    {
        $user_online = \App\User::find($request->user_id);
        // $user_online->online_status = 0;
        // $user_online->save();

        return response()->json(['success' => true, 'data' => $user_online], 200);
    }

    public function AccountPlan(Request $request)
    {
        $data = new \App\AccountPlan;

        if (isset($request->role)) {
            if ($request->role == 'Athlete') {
                $data = $data->where('account_type_id', 1);
            } else if ($request->role == 'Athlete Guardian') {
                $data = $data->where('account_type_id', 2);
            }
        }

        $data = $data->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
    public function AccountType(Request $request)
    {
        $data = \App\AccountType::with(['account_plan', 'privacy']);

        $data = $data->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    public function forgot_password(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $token = $user->createToken('cancer-caregiver')->accessToken;

            $to_name = $user->firstname;
            // $to_email = $request->email;

            $email_temp1 = explode('+', $request->email);
            $email_temp2 = explode('@', $request->email);

            $to_email = count($email_temp1) == 2 ? $email_temp1[0] . "@" . $email_temp2[1] : $email_temp1[0];

            $data = [
                'to_name'       => $to_name,
                'to_email'      => $to_email,
                'link_origin'   => $request->link,
                'token'         => $token,
                'username'      => $user->username,
                'link'          => $request->link . '/forgot-password' . "/" . $token . '/' . $user->id,
            ];
            $this->setup_email_template('FORGOT / CHANGE PASSWORD', $data);

            return response()->json(['success' => true, 'token' => $token]);
        } else {
            return response()->json(['success' => false, 'error' => 'Email Address Not Found'], 401);
        }
    }

    public function change_password(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        if ($user) {
            $user->password = Hash::make($request->new_password);
            $user->save();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'error' => 'Email Address Not Found'], 401);
        }
    }

    public function verifypassword(Request $request)
    {
        $check = User::find($request->user_id);

        if (isset($request->password)) {
            if ($check) {
                if (Hash::check($request->password, $check->password)) {
                    return response()->json([
                        'success' => true,
                        'message' => "Authenticated"

                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "Not authenticated"
                    ], 200);
                }
            }
        }
    }

    public function generate2faSecret(Request $request)
    {
        $user = auth()->user();
        $email_temp1 = explode('+', $user->email);
        $email_temp2 = explode('@', $user->email);

        $email_new = count($email_temp1) == 2 ? $email_temp1[0] . "@" . $email_temp2[1] : $email_temp1[0];

        // Initialise the 2FA class
        $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());

        //generate
        $key = $google2fa->generateSecretKey();

        $QR_Image = $google2fa->getQRCodeInline(
            'CCG',
            $email_new,
            $key
        );

        //save key to user db
        $user->google2fa_secret = $key;
        $user->save();

        return response()->json([
            'success' => true,
            'data' => $key,
            'google_url' => $QR_Image
        ], 200);
    }


    public function enable2fa(Request $request)
    {

        $user =  auth()->user();
        // Initialise the 2FA class
        $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());

        $code = $request->code;
        $valid = $google2fa->verifyKey($user->google2fa_secret, $code);

        if ($valid) {
            $user->google2fa_enable = 1;
            $user->save();

            return response()->json([
                'success' => true,
                'mesage' => "2FA is enabled successfully",
                'valid' => $valid

            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Invalid verification Code, Please try again.",
                'valid' => $valid

            ], 200);
        }
    }

    public function disable2fa(Request $request)
    {

        $user =  auth()->user();
        $user->google2fa_enable = 0;
        $user->google2fa_secret = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => '2FA is disabled successfully'
        ], 200);
    }

    public function verify2fa(Request $request)
    {
        $user = User::find($request->id);

        //  Initialise the 2FA class
        $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());

        $code = $request->code;
        $valid = $google2fa->verifyKey($user->google2fa_secret, $code);

        if ($valid) {
            $token = $user->createToken('ccg')->accessToken;

            return response()->json([
                'success' => true,
                'data' => $user,
                'token' => $token,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Authenticator Code, Please try again'
            ], 200);
        }
    }

    public function viewas(Request $request)
    {

        $id = $request->id;
        $admin = auth()->user();

        if ($admin->role == "Super Admin" || $admin->role == "Admin" || $request->viewas == "true") {

            $user = \App\User::find($id);
            $token = $user->createToken('ccg')->accessToken;

            return response()->json([
                'success' => true,
                'data' => $user,
                'token' => $token,

            ], 200);
        }
    }
}