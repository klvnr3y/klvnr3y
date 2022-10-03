<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\UserPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\UserPayment  $userPayment
     * @return \Illuminate\Http\Response
     */
    public function show(UserPayment $userPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UserPayment  $userPayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserPayment $userPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UserPayment  $userPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserPayment $userPayment)
    {
        //
    }

    public function revenue_graph_per_year()
    {
        $data_series_name   = [];
        $data_series_value  = [];

        $year_from = date("Y") - 2;
        $year_to = date("Y");

        for ($x = $year_from; $x <= $year_to; $x++) {
            $data_series_name[] = "$x";

            $data_result = \App\UserPayment::whereYear('user_payments.date_paid', $x)->sum("amount");

            $data_series_value[] = [
                "name"  => "$x",
                "color"  => "#027273",
                "y"  => $data_result
            ];
        }

        $data = [
            "data_series_name"  => $data_series_name,
            "data_series_value" => $data_series_value,
            "action"            => "yearly",
            "downTo"            => "quarterly"
        ];

        $ret = [
            'success'   => true,
            'data'      => $data
        ];

        return response()->json($ret, 200);
    }

    public function revenue_all(Request $request)
    {
        $date_start = date("Y-m-d", strtotime($request->date_start));
        $date_end = date("Y-m-d", strtotime($request->date_end));
        $type = $request->type;

        $data_result = \App\UserPayment::whereDate('user_payments.date_paid', ">=", $date_start)
            ->whereDate('user_payments.date_paid', "<=", $date_end)
            ->users();

        if ($type != "ALL") {
            $data_result = $data_result->where("role", $type);
        }

        $ret = [
            'success'   => true,
            'data'      => $data_result->sum("amount"),
            // "date_start" => $date_start,
            // "date_end" => $date_end,
            // "type" => $type,
        ];

        return response()->json($ret, 200);
    }

    public function revenue_per_month(Request $request)
    {
        $year = $request->year;
        $month = $request->month;

        $data_result1 = \App\UserPayment::whereYear('user_payments.date_paid', "=", $year)
            ->whereMonth('user_payments.date_paid', "=", $month)
            ->where('role', "=", "Cancer Caregiver")
            ->users()
            ->sum('amount');
        $data_result2 = \App\UserPayment::whereYear('user_payments.date_paid', "=", $year)
            ->whereMonth('user_payments.date_paid', "=", $month)
            ->where('role', "=", "Cancer Care Professional")
            ->users()
            ->sum('amount');


        $ret = [
            'success'   => true,
            'data'      => [
                [
                    'name' => "Cancer Caregiver",
                    'y' => $data_result1,
                    'color' => "#027273",
                ],
                [
                    'name' => "Cancer Care Professional",
                    'y' => $data_result2,
                    'color' => "#e4151f",
                ],
            ],
        ];

        return response()->json($ret, 200);
    }

    public function revenue_per_state(Request $request)
    {
        $type = $request->type;
        $state = $request->state;

        $data = [];

        if ($type == 'ALL') {
            $data_result1 = \App\UserPayment::where(DB::raw('(SELECT state FROM user_addresses WHERE user_payments.user_id=user_addresses.user_id AND is_primary=1 LIMIT 1)'), $state)
                ->where('role', "=", "Cancer Caregiver")
                ->users()
                ->sum('amount');
            $data_result2 = \App\UserPayment::where(DB::raw('(SELECT state FROM user_addresses WHERE user_payments.user_id=user_addresses.user_id AND is_primary=1 LIMIT 1)'), $state)
                ->where('role', "=", "Cancer Care Professional")
                ->users()
                ->sum('amount');

            $data = [
                [
                    'name' => "Cancer Caregiver",
                    'y' => $data_result1,
                    'color' => "#027273",
                ],
                [
                    'name' => "Cancer Care Professional",
                    'y' => $data_result2,
                    'color' => "#e4151f",
                ],
            ];
        } else if ($type == 'Cancer Caregiver') {
            $data_result1 = \App\UserPayment::where(DB::raw('(SELECT state FROM user_addresses WHERE user_payments.user_id=user_addresses.user_id AND is_primary=1 LIMIT 1)'), $state)
                ->where('role', "=", "Cancer Caregiver")
                ->users()
                ->sum('amount');

            $data = [
                [
                    'name' => "Cancer Caregiver",
                    'y' => $data_result1,
                    'color' => "#027273",
                ],
            ];
        } else if ($type == 'Cancer Care Professional') {
            $data_result2 = \App\UserPayment::where(DB::raw('(SELECT state FROM user_addresses WHERE user_payments.user_id=user_addresses.user_id AND is_primary=1 LIMIT 1)'), $state)
                ->where('role', "=", "Cancer Care Professional")
                ->users()
                ->sum('amount');

            $data = [
                [
                    'name' => "Cancer Care Professional",
                    'y' => $data_result2,
                    'color' => "#e4151f",
                ],
            ];
        }

        $ret = [
            'success'   => true,
            'data'      => $data,
        ];

        return response()->json($ret, 200);
    }


    public function revenue_by_filter(Request $request)
    {
        $date_from = date("Y-m-d", strtotime($request->date_from));
        $date_to = date("Y-m-d", strtotime($request->date_to));

        $total_subscriber = "(SELECT COUNT( DISTINCT user_id ) FROM user_payments LEFT JOIN users ON user_payments.user_id = users.id WHERE DATE( date_paid ) >= '$date_from' AND DATE( date_paid ) <= '$date_to'";
        if ($request->name) {
            $total_subscriber .= " AND (SELECT IF(firstname IS NOT NULL, firstname, CONCAT(`firstname`, ' ', `lastname`))) LIKE '%$request->name%'";
        }
        if ($request->type != 'ALL') {
            $total_subscriber .= " AND role = '$request->type'";
        } else {
            $total_subscriber .= " AND role != 'Admin'";
        }
        $total_subscriber .= " ) total_subscriber";

        $data = UserPayment::select([
            "user_payments.id",
            // DB::raw('MIN(date_paid) from_date'),
            // DB::raw('MAX(date_paid) to_date'),
            DB::raw($total_subscriber),
            DB::raw("SUM(amount) total_revenue"),
            DB::raw("(SELECT SUM(amount) FROM user_payments LEFT JOIN users ON user_payments.user_id = users.id WHERE role = 'Cancer Caregiver' AND DATE( date_paid ) >= '$date_from' AND DATE( date_paid ) <= '$date_to' ) caregiver_revenue"),
            DB::raw("(SELECT SUM(amount) FROM user_payments LEFT JOIN users ON user_payments.user_id = users.id WHERE role = 'Cancer Care Professional' AND DATE( date_paid ) >= '$date_from' AND DATE( date_paid ) <= '$date_to' ) careprof_revenue"),
        ])
            ->users()
            ->whereDate('date_paid', '>=', $date_from)
            ->whereDate('date_paid', '<=', $date_to);

        if ($request->name) {
            $data = $data->where(DB::raw("(SELECT IF(firstname IS NOT NULL, firstname, CONCAT(`firstname`, ' ', `lastname`)))"), 'LIKE', "%$request->name%");
        }

        if ($request->type != 'ALL') {
            $data = $data->where('role', $request->type);
        }

        $data = $data->get();

        $data[0]['from_date'] = $date_from;
        $data[0]['to_date'] = $date_to;

        $ret = [
            'success'   => true,
            'data'      => $data,
        ];

        return response()->json($ret, 200);
    }

    public function revenue_table_by_filter(Request $request)
    {
        $date_from = date("Y-m-d", strtotime($request->date_from));
        $date_to = date("Y-m-d", strtotime($request->date_to));

        $data = UserPayment::select([
            "user_payments.*",
            "firstname",
            "lastname",
            "contact_number",
            "email",
            "role",
            DB::raw("(SELECT state FROM user_addresses WHERE user_payments.user_id = user_addresses.user_id AND is_primary=1) state")
        ])
            ->users()
            ->whereDate('date_paid', '>=', $date_from)
            ->whereDate('date_paid', '<=', $date_to);

        if ($request->name) {
            $data = $data->where(DB::raw("(SELECT IF(firstname IS NOT NULL, firstname, CONCAT(`firstname`, ' ', `lastname`)))"), 'LIKE', "%$request->name%");
        }

        if ($request->type != 'ALL') {
            $data = $data->where('role', $request->type);
        }

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

        if ($request->page_size) {
            $data = $data
                ->limit($request->page_size)
                ->paginate($request->page_size, ['*'], 'page', $request->page_number)->toArray();
        } else {
            $data = $data->get();
        }

        $ret = [
            'success'   => true,
            'data'      => $data
        ];

        return response()->json($ret, 200);
    }
}